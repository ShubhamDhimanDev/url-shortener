@extends('layouts.app')
@section('title', $team->name)
@section('page-title', $team->name)

@section('content')
<div class="space-y-6">

    {{-- ── Team header ─────────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-6 flex items-center gap-4">
        <div class="size-14 rounded-xl bg-primary/20 flex items-center justify-center text-primary font-bold text-lg shrink-0">
            {{ strtoupper(substr($team->name, 0, 2)) }}
        </div>
        <div class="flex-1">
            <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">{{ $team->name }}</h2>
            @if($team->description)
                <p class="text-sm text-slate-400">{{ $team->description }}</p>
            @endif
        </div>
        @if($team->owner_id === $user->id)
        <a href="{{ route('app.teams.settings', $team->ulid) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-slate-200 dark:border-border-dark text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary rounded-lg transition">
            <span class="material-symbols-outlined !text-sm">settings</span> Settings
        </a>
        @endif
    </div>

    {{-- ── Members ──────────────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Members ({{ $members->count() }})</h3>
            @if($team->owner_id === $user->id)
            <a href="{{ route('app.teams.settings', $team->ulid) }}#invite"
               class="text-xs text-primary hover:underline">Invite member →</a>
            @endif
        </div>

        <div class="divide-y divide-slate-200 dark:divide-border-dark">
            @foreach($members as $member)
                <div class="py-3 flex items-center gap-3">
                    <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-semibold shrink-0">
                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $member->user->name }}</p>
                        <p class="text-xs text-slate-400">{{ $member->user->email }}</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full @switch($member->role)
                        @case('owner')  bg-primary/10 text-primary @break
                        @case('admin')  bg-blue-100 dark:bg-blue-900/30 text-blue-600 @break
                        @default        bg-slate-100 dark:bg-slate-800 text-slate-500
                        @endswitch capitalize font-medium">
                        {{ $member->role }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
