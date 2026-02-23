<?php

namespace App\Http\Controllers\App;

use App\Actions\Links\CreateLinkAction;
use App\Actions\Links\DeleteLinkAction;
use App\Actions\Links\UpdateLinkAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Links\StoreLinkRequest;
use App\Http\Requests\Links\UpdateLinkRequest;
use App\Models\Domain;
use App\Models\Link;
use App\Models\LinkTag;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function __construct(
        private readonly CreateLinkAction   $createLink,
        private readonly UpdateLinkAction   $updateLink,
        private readonly DeleteLinkAction   $deleteLink,
        private readonly AnalyticsService   $analytics,
    ) {}

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user = $request->user();
        $team = $this->resolveActiveTeam($user);

        $query = Link::query()
            ->when($team, fn ($q) => $q->where('team_id', $team->id))
            ->when(! $team, fn ($q) => $q->where('user_id', $user->id)->whereNull('team_id'))
            ->with(['domain', 'tags', 'qrCode'])
            ->withCount('clicks');

        // Search
        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('destination_url', 'like', "%{$search}%")
                ->orWhere('short_code', 'like', "%{$search}%")
            );
        }

        // Filter by status
        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        // Filter by tag
        if ($tagId = $request->input('tag_id')) {
            $query->whereHas('tags', fn ($q) => $q->where('link_tags.id', $tagId));
        }

        // Filter by domain
        if ($domainId = $request->input('domain_id')) {
            $query->where('domain_id', $domainId);
        }

        // Sort
        $sortMap = [
            'latest'  => ['created_at', 'desc'],
            'oldest'  => ['created_at', 'asc'],
            'clicks'  => ['clicks_count', 'desc'],
            'title'   => ['title', 'asc'],
        ];
        [$sortColumn, $sortDir] = $sortMap[$request->input('sort', 'latest')] ?? ['created_at', 'desc'];
        $query->orderBy($sortColumn, $sortDir);

        $links = $query->paginate(15)->withQueryString();

        // Filter options
        $tags = LinkTag::query()
            ->when($team, fn ($q) => $q->where('team_id', $team->id))
            ->when(! $team, fn ($q) => $q->where('user_id', $user->id)->whereNull('team_id'))
            ->get();

        $domains = Domain::query()
            ->where('is_verified', true)
            ->where('is_active', true)
            ->when($team, fn ($q) => $q->where('team_id', $team->id))
            ->when(! $team, fn ($q) => $q->where('user_id', $user->id)->whereNull('team_id'))
            ->get();

        return view('app.links.index', compact('links', 'tags', 'domains', 'team'));
    }

    // ─── Create / Store ───────────────────────────────────────────────────────

    public function create(Request $request): View
    {
        $user = $request->user();
        $team = $this->resolveActiveTeam($user);

        $domains = Domain::query()
            ->where('is_verified', true)
            ->where('is_active', true)
            ->when($team, fn ($q) => $q->where('team_id', $team->id))
            ->when(! $team, fn ($q) => $q->where('user_id', $user->id)->whereNull('team_id'))
            ->get();

        $tags = LinkTag::query()
            ->when($team, fn ($q) => $q->where('team_id', $team->id))
            ->when(! $team, fn ($q) => $q->where('user_id', $user->id)->whereNull('team_id'))
            ->orderBy('name')
            ->get();

        return view('app.links.create', compact('domains', 'tags', 'team'));
    }

    public function store(StoreLinkRequest $request): RedirectResponse
    {
        $user = $request->user();
        $team = $this->resolveActiveTeam($user);

        $link = $this->createLink->execute($user, $team, $request->validated());

        return redirect()->route('app.links.show', $link->ulid)
            ->with('success', 'Link created successfully!');
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(Request $request, string $ulid): View
    {
        $link = $this->findLink($ulid, $request->user());

        $this->authorize('view', $link);

        $from    = Carbon::parse($request->input('from', now()->subDays(29)->toDateString()));
        $to      = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();
        $summary = $this->analytics->getSummary($link, $from, $to);

        return view('app.links.show', compact('link', 'summary', 'from', 'to'));
    }

    // ─── Edit / Update ────────────────────────────────────────────────────────

    public function edit(Request $request, string $ulid): View
    {
        $user = $request->user();
        $link = $this->findLink($ulid, $user);

        $this->authorize('update', $link);

        $team = $link->team;

        $domains = Domain::query()
            ->where('is_verified', true)
            ->where('is_active', true)
            ->when($team, fn ($q) => $q->where('team_id', $team->id))
            ->when(! $team, fn ($q) => $q->where('user_id', $user->id)->whereNull('team_id'))
            ->get();

        $tags = LinkTag::query()
            ->when($team, fn ($q) => $q->where('team_id', $team->id))
            ->when(! $team, fn ($q) => $q->where('user_id', $user->id)->whereNull('team_id'))
            ->orderBy('name')
            ->get();

        $selectedTagIds = $link->tags->pluck('id')->toArray();

        return view('app.links.edit', compact('link', 'domains', 'tags', 'selectedTagIds', 'team'));
    }

    public function update(UpdateLinkRequest $request, string $ulid): RedirectResponse
    {
        $link = $this->findLink($ulid, $request->user());
        $this->authorize('update', $link);

        $this->updateLink->execute($link, $request->validated());

        return redirect()->route('app.links.show', $link->ulid)
            ->with('success', 'Link updated successfully!');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(Request $request, string $ulid): RedirectResponse
    {
        $link = $this->findLink($ulid, $request->user());
        $this->authorize('delete', $link);

        $this->deleteLink->execute($link);

        return redirect()->route('app.links.index')
            ->with('success', 'Link deleted.');
    }

    // ─── Toggle active ────────────────────────────────────────────────────────

    public function toggle(Request $request, string $ulid): RedirectResponse
    {
        $link = $this->findLink($ulid, $request->user());
        $this->authorize('update', $link);

        $link->update(['is_active' => ! $link->is_active]);

        return back()->with(
            'success',
            $link->is_active ? 'Link activated.' : 'Link deactivated.'
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function findLink(string $ulid, $user): Link
    {
        return Link::where('ulid', $ulid)
            ->where(fn ($q) => $q
                ->where('user_id', $user->id)
                ->orWhereIn('team_id', $user->teamMemberships()->pluck('team_id'))
            )
            ->firstOrFail();
    }

    private function resolveActiveTeam($user)
    {
        $teamId = session('active_team_id');
        if ($teamId) {
            return $user->teamMemberships()->where('team_id', $teamId)->first()?->team;
        }
        return null;
    }
}
