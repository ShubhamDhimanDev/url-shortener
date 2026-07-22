@extends('layouts.app')
@section('title', 'Team Settings — ' . $team->name)
@section('page-title', 'Team Settings')

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- ── Edit team details ────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Team Details</h3>

        <form method="POST" action="{{ route('app.teams.update', $team->ulid) }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label for="name" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">
                    Team Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name"
                       value="{{ old('name', $team->name) }}"
                       class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
            </div>

            <div>
                <label for="description" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition resize-none">{{ old('description', $team->description) }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                    <span class="material-symbols-outlined !text-base">save</span>
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- ── Invite member ────────────────────────────────────────────────── --}}
    <div id="invite" class="glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">
            <span class="material-symbols-outlined !text-base text-primary align-middle mr-1">person_add</span>
            Invite Member
        </h3>

        <form method="POST" action="{{ route('app.teams.invite', $team->ulid) }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Email Address</label>
                <input type="email" name="email"
                       placeholder="colleague@example.com"
                       class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
                @error('email')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Role</label>
                <select name="role"
                        class="text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="member">Member</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-base">send</span>
                Send Invite
            </button>
        </form>
    </div>

    {{-- ── Manage members ────────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Members</h3>

        <div class="divide-y divide-slate-200 dark:divide-border-dark">
            @foreach($members as $member)
                <div class="py-3 flex items-center gap-3">
                    <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-semibold shrink-0">
                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100">
                            {{ $member->user->name }}
                            @if($member->user_id === $team->owner_id)
                                <span class="text-[10px] font-medium text-primary">(you)</span>
                            @endif
                        </p>
                        <p class="text-xs text-slate-400">{{ $member->user->email }}</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full capitalize font-medium
                        @if($member->role === 'owner') bg-primary/10 text-primary
                        @elseif($member->role === 'admin') bg-blue-100 dark:bg-blue-900/30 text-blue-600
                        @else bg-slate-100 dark:bg-slate-800 text-slate-500 @endif">
                        {{ $member->role }}
                    </span>
                    @if($member->role !== 'owner')
                        <form method="POST" action="{{ route('app.teams.members.remove', [$team->ulid, $member->id]) }}"
                              onsubmit="return confirm('Remove {{ $member->user->name }} from the team?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-slate-400 hover:text-red-500 transition">
                                <span class="material-symbols-outlined !text-base">person_remove</span>
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Danger zone ──────────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-6 border border-red-200 dark:border-red-900/50">
        <h3 class="text-sm font-semibold text-red-600 mb-2">Danger Zone</h3>
        <p class="text-xs text-slate-400 mb-4">Deleting a team will remove all its members. Links will be transferred to your personal account.</p>
        <button type="button"
                onclick="confirm('Are you sure you want to delete {{ $team->name }}? This cannot be undone.') && document.getElementById('delete-team-form').submit()"
                class="inline-flex items-center gap-1.5 px-4 py-2 border border-red-400 text-red-500 text-sm font-medium rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition">
            <span class="material-symbols-outlined !text-sm">delete_forever</span>
            Delete Team
        </button>
        {{-- We'd need a delete route for teams from the app panel; skipping for now --}}
    </div>

</div>
@endsection
