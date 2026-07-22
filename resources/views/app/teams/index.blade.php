@extends('layouts.app')
@section('title', 'Teams')
@section('page-title', 'Teams')

@section('content')
<div class="space-y-6">

    {{-- Create team CTA --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">Collaborate with your team to manage links together.</p>
        <a href="{{ route('app.teams.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
            <span class="material-symbols-outlined !text-base">add</span>
            New Team
        </a>
    </div>

    {{-- Teams I own --}}
    @if($ownedTeams->isNotEmpty())
    <div>
        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Teams You Own</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($ownedTeams as $team)
            <div class="glass-card rounded-xl p-5 neon-glow">
                <div class="flex items-start gap-3">
                    <div class="size-10 rounded-xl bg-primary/20 flex items-center justify-center text-primary font-bold text-sm shrink-0">
                        {{ strtoupper(substr($team->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $team->name }}</h4>
                        @if($team->description)
                            <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $team->description }}</p>
                        @endif
                        <p class="text-[11px] text-slate-400 mt-1">{{ $team->members_count }} member{{ $team->members_count !== 1 ? 's' : '' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <a href="{{ route('app.teams.show', $team->ulid) }}"
                       class="flex-1 text-center py-2 text-xs font-medium border border-slate-200 dark:border-border-dark text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary rounded-lg transition">
                        View Team
                    </a>
                    <a href="{{ route('app.teams.settings', $team->ulid) }}"
                       class="p-2 border border-slate-200 dark:border-border-dark text-slate-500 hover:border-primary hover:text-primary rounded-lg transition">
                        <span class="material-symbols-outlined !text-sm">settings</span>
                    </a>
                    <form method="POST" action="{{ route('app.teams.switch-context') }}">
                        @csrf
                        <input type="hidden" name="team_id" value="{{ $team->id }}">
                        <button type="submit"
                                class="p-2 border border-slate-200 dark:border-border-dark text-slate-500 hover:border-green-400 hover:text-green-500 rounded-lg transition"
                                title="Switch to this team">
                            <span class="material-symbols-outlined !text-sm">swap_horiz</span>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Teams I'm a member of --}}
    @if($memberTeams->isNotEmpty())
    <div>
        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Teams You're In</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($memberTeams as $membership)
            @php $team = $membership->team; @endphp
            <div class="glass-card rounded-xl p-5">
                <div class="flex items-start gap-3">
                    <div class="size-10 rounded-xl bg-slate-200 dark:bg-slate-800 flex items-center justify-center font-bold text-sm shrink-0">
                        {{ strtoupper(substr($team->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $team->name }}</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Owned by {{ $team->owner->name }} · Role: <span class="capitalize font-medium">{{ $membership->role }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <a href="{{ route('app.teams.show', $team->ulid) }}"
                       class="flex-1 text-center py-2 text-xs font-medium border border-slate-200 dark:border-border-dark text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary rounded-lg transition">
                        View Team
                    </a>
                    <form method="POST" action="{{ route('app.teams.switch-context') }}">
                        @csrf
                        <input type="hidden" name="team_id" value="{{ $team->id }}">
                        <button type="submit"
                                class="p-2 border border-slate-200 dark:border-border-dark text-slate-500 hover:border-green-400 hover:text-green-500 rounded-lg transition"
                                title="Switch to this team">
                            <span class="material-symbols-outlined !text-sm">swap_horiz</span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('app.teams.leave', $team->ulid) }}"
                          onsubmit="return confirm('Leave {{ $team->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="p-2 border border-slate-200 dark:border-border-dark text-slate-500 hover:border-red-400 hover:text-red-500 rounded-lg transition"
                                title="Leave team">
                            <span class="material-symbols-outlined !text-sm">exit_to_app</span>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Empty state --}}
    @if($ownedTeams->isEmpty() && $memberTeams->isEmpty())
    <div class="glass-card rounded-xl p-12 text-center">
        <span class="material-symbols-outlined !text-5xl text-slate-300 dark:text-slate-700">groups</span>
        <p class="mt-3 text-slate-500 dark:text-slate-400">You're not part of any team yet.</p>
        <a href="{{ route('app.teams.create') }}"
           class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
            <span class="material-symbols-outlined !text-base">add</span>
            Create a Team
        </a>
    </div>
    @endif

</div>
@endsection
