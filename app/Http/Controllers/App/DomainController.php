<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\StoreDomainRequest;
use App\Jobs\ProvisionDomainSslJob;
use App\Models\Domain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DomainController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user = $request->user();
        $team = $this->resolveActiveTeam($user);

        $domains = Domain::query()
            ->when($team, fn ($q) => $q->where('team_id', $team->id))
            ->when(! $team, fn ($q) => $q->where('user_id', $user->id)->whereNull('team_id'))
            ->withCount('links')
            ->latest()
            ->paginate(15);

        return view('app.domains.index', compact('domains', 'team'));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create(Request $request): View
    {
        $team = $this->resolveActiveTeam($request->user());
        return view('app.domains.create', compact('team'));
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(StoreDomainRequest $request): RedirectResponse
    {
        $user = $request->user();
        $team = $this->resolveActiveTeam($user);

        $domain = Domain::create([
            'user_id'            => $user->id,
            'team_id'            => $team?->id,
            'domain'             => strtolower($request->input('domain')),
            'type'               => $request->input('type', 'custom_domain'),
            'is_verified'        => false,
            'is_active'          => true,
            'verification_token' => 'urlshort-verify-' . Str::random(32),
            'ssl_status'         => 'pending',
        ]);

        return redirect()->route('app.domains.index')
            ->with('success', "Domain added! Please add the TXT record to verify ownership.");
    }

    // ─── Verify ───────────────────────────────────────────────────────────────

    public function verify(Request $request, Domain $domain): RedirectResponse
    {
        $this->authorize('update', $domain);

        // DNS TXT record check — query _verify subdomain to avoid CNAME conflict
        $verifyHost = '_verify.' . $domain->domain;
        $records = @dns_get_record($verifyHost, DNS_TXT) ?: [];
        $verified = collect($records)
            ->contains(fn ($r) => str_contains($r['txt'] ?? '', $domain->verification_token));

        if ($verified) {
            $domain->update([
                'is_verified' => true,
                'verified_at' => now(),
                'ssl_status'  => 'pending',
            ]);

            // Automatically provision SSL certificate in the background
            ProvisionDomainSslJob::dispatch($domain)->onQueue('default');

            return back()->with('success', 'Domain verified! SSL certificate is being provisioned (may take 1–2 minutes).');
        }

        return back()->with('error', 'Verification failed. Please check your DNS records and try again.');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(Request $request, Domain $domain): RedirectResponse
    {
        $this->authorize('delete', $domain);
        $domain->delete();

        return redirect()->route('app.domains.index')
            ->with('success', 'Domain removed.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function resolveActiveTeam($user)
    {
        $teamId = session('active_team_id');
        if ($teamId) {
            return $user->teamMemberships()->where('team_id', $teamId)->first()?->team;
        }
        return null;
    }
}
