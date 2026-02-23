<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UpdateTeamRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $query = Team::with(['owner', 'subscription.plan'])
            ->withTrashed()
            ->withCount('members');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->input('status') === 'active') {
            $query->whereNull('deleted_at')->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        } elseif ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        }

        $teams = $query->latest()->paginate(25)->withQueryString();

        return view('super-admin.teams.index', compact('teams'));
    }

    public function show(Team $team): View
    {
        $team->loadMissing([
            'owner',
            'members.user',
            'subscription.plan',
            'links' => fn ($q) => $q->latest()->limit(10),
            'invoices' => fn ($q) => $q->latest()->limit(5),
        ]);

        $linkCount    = $team->links()->count();
        $clicksTotal  = $team->links()->sum('clicks_count');

        return view('super-admin.teams.show', compact('team', 'linkCount', 'clicksTotal'));
    }

    public function edit(Team $team): View
    {
        return view('super-admin.teams.edit', compact('team'));
    }

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $team->update($request->only('name', 'description', 'is_active'));

        return redirect()
            ->route('super-admin.teams.show', $team)
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $team->delete();

        return redirect()
            ->route('super-admin.teams.index')
            ->with('success', 'Team deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        $team = Team::withTrashed()->findOrFail($id);
        $team->restore();

        return back()->with('success', 'Team restored successfully.');
    }
}
