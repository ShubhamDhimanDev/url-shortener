<?php

namespace App\Http\Controllers\App;

use App\Actions\Teams\CreateTeamAction;
use App\Actions\Teams\InviteMemberAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\StoreTeamRequest;
use App\Http\Requests\App\InviteTeamMemberRequest;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct(
        private readonly CreateTeamAction  $createTeam,
        private readonly InviteMemberAction $inviteMember,
    ) {}

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user = $request->user();

        // Teams the user owns
        $ownedTeams = Team::where('owner_id', $user->id)->withCount('members')->get();

        // Teams the user is a member of (not owner)
        $memberTeams = $user->teamMemberships()
            ->with('team.owner')
            ->get()
            ->filter(fn ($m) => $m->team->owner_id !== $user->id);

        return view('app.teams.index', compact('ownedTeams', 'memberTeams'));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('app.teams.create');
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $team = $this->createTeam->execute($request->user(), $request->validated());

        session(['active_team_id' => $team->id]);

        return redirect()->route('app.teams.show', $team->ulid)
            ->with('success', 'Team created successfully!');
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(Request $request, string $ulid): View
    {
        $user = $request->user();
        $team = Team::where('ulid', $ulid)->firstOrFail();

        $this->authorize('view', $team);

        $members = $team->members()->with('user')->get();

        return view('app.teams.show', compact('team', 'members', 'user'));
    }

    // ─── Settings ────────────────────────────────────────────────────────────

    public function settings(Request $request, string $ulid): View
    {
        $team = Team::where('ulid', $ulid)->firstOrFail();
        $this->authorize('update', $team);

        $members = $team->members()->with('user')->get();

        return view('app.teams.settings', compact('team', 'members'));
    }

    public function update(StoreTeamRequest $request, string $ulid): RedirectResponse
    {
        $team = Team::where('ulid', $ulid)->firstOrFail();
        $this->authorize('update', $team);

        $team->update($request->only(['name', 'description', 'avatar']));

        return back()->with('success', 'Team updated.');
    }

    // ─── Invite member ────────────────────────────────────────────────────────

    public function invite(InviteTeamMemberRequest $request, string $ulid): RedirectResponse
    {
        $team = Team::where('ulid', $ulid)->firstOrFail();
        $this->authorize('update', $team);

        $this->inviteMember->execute($team, $request->input('email'), $request->input('role', 'member'));

        return back()->with('success', 'Invitation sent!');
    }

    // ─── Remove member ────────────────────────────────────────────────────────

    public function removeMember(Request $request, string $ulid, int $memberId): RedirectResponse
    {
        $team = Team::where('ulid', $ulid)->firstOrFail();
        $this->authorize('update', $team);

        $member = TeamMember::where('id', $memberId)->where('team_id', $team->id)->firstOrFail();

        // Cannot remove the owner
        if ($member->role === 'owner') {
            return back()->with('error', 'Cannot remove the team owner.');
        }

        $member->delete();

        return back()->with('success', 'Member removed from team.');
    }

    // ─── Switch active team ───────────────────────────────────────────────────

    public function switchContext(Request $request): RedirectResponse
    {
        $teamId = $request->input('team_id');

        if (! $teamId) {
            session()->forget('active_team_id');
            return back()->with('success', 'Switched to personal workspace.');
        }

        $user = $request->user();
        $membership = $user->teamMemberships()->where('team_id', $teamId)->first();

        if (! $membership) {
            return back()->with('error', 'You are not a member of that team.');
        }

        session(['active_team_id' => $teamId]);

        return back()->with('success', 'Switched to ' . $membership->team->name . '.');
    }

    // ─── Leave team ───────────────────────────────────────────────────────────

    public function leave(Request $request, string $ulid): RedirectResponse
    {
        $team = Team::where('ulid', $ulid)->firstOrFail();
        $user = $request->user();

        if ($team->owner_id === $user->id) {
            return back()->with('error', 'Team owners cannot leave. Transfer ownership first.');
        }

        TeamMember::where('team_id', $team->id)->where('user_id', $user->id)->delete();

        if (session('active_team_id') === $team->id) {
            session()->forget('active_team_id');
        }

        return redirect()->route('app.teams.index')->with('success', 'You have left the team.');
    }
}
