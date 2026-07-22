<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\LinkClick;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $team = $this->resolveActiveTeam($user);
        $entity = $team ?? $user;

        $featureService = FeatureService::for($entity);

        // ── Links stats ───────────────────────────────────────────────────
        $linksQuery = Link::query()
            ->when($team, fn ($q) => $q->where('team_id', $team->id))
            ->when(! $team, fn ($q) => $q->where('user_id', $user->id)->whereNull('team_id'));

        $totalLinks  = (clone $linksQuery)->count();
        $activeLinks = (clone $linksQuery)->where('is_active', true)->count();

        // ── Click stats ───────────────────────────────────────────────────
        $linkIds = (clone $linksQuery)->pluck('id');

        $clicksToday = LinkClick::whereIn('link_id', $linkIds)
            ->whereDate('clicked_at', Carbon::today())
            ->where('is_bot', false)
            ->count();

        $clicksThisMonth = LinkClick::whereIn('link_id', $linkIds)
            ->whereBetween('clicked_at', [Carbon::now()->startOfMonth(), Carbon::now()])
            ->where('is_bot', false)
            ->count();

        // ── Recent links ──────────────────────────────────────────────────
        $recentLinks = (clone $linksQuery)
            ->with(['clicks' => fn ($q) => $q->where('is_bot', false)->whereDate('clicked_at', Carbon::today())])
            ->withCount('clicks')
            ->latest()
            ->limit(5)
            ->get();

        // ── Clicks over last 14 days ──────────────────────────────────────
        $rawClicks = LinkClick::select(
                DB::raw('DATE(clicked_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereIn('link_id', $linkIds)
            ->where('is_bot', false)
            ->where('clicked_at', '>=', Carbon::now()->subDays(13))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $clicksChart = collect();
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $clicksChart->put($date, $rawClicks->get($date, 0));
        }

        // ── Plan usage ────────────────────────────────────────────────────
        $linksLimit     = $featureService->value('links_per_month');
        $linksUsed      = (clone $linksQuery)->whereBetween('created_at', [
            Carbon::now()->startOfMonth(), Carbon::now(),
        ])->count();
        $linksRemaining = $featureService->remaining('links_per_month');

        // ── Subscription / plan ───────────────────────────────────────────
        $subscription = $entity->subscription;
        $activePlan   = $subscription?->plan;

        return view('app.dashboard', compact(
            'totalLinks',
            'activeLinks',
            'clicksToday',
            'clicksThisMonth',
            'recentLinks',
            'clicksChart',
            'linksLimit',
            'linksUsed',
            'linksRemaining',
            'subscription',
            'activePlan',
            'featureService',
            'team',
        ));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function resolveActiveTeam($user)
    {
        // Check session for active team context
        $teamId = session('active_team_id');
        if ($teamId) {
            return $user->teamMemberships()->where('team_id', $teamId)->first()?->team;
        }
        return null;
    }
}
