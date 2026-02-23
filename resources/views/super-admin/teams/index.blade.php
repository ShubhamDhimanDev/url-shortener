@extends('layouts.super-admin')
@section('title', 'Teams')
@section('page-title', 'Teams')

@section('content')
<div class="space-y-4">

    {{-- Filters --}}
    <form method="GET" class="glass-card rounded-xl p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-xs text-slate-400 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Name or slug…"
                   class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary" />
        </div>
        <div class="min-w-36">
            <label class="block text-xs text-slate-400 mb-1">Status</label>
            <select name="status" class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
                <option value="">All</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                <option value="deleted" @selected(request('status') === 'deleted')>Deleted</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">Filter</button>
            <a href="{{ route('super-admin.teams.index') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-sm rounded-lg">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="border-b border-border-dark">
                <tr class="text-xs text-slate-400 uppercase tracking-wider">
                    <th class="text-left px-5 py-3">Team</th>
                    <th class="text-left px-5 py-3">Owner</th>
                    <th class="text-left px-5 py-3">Members</th>
                    <th class="text-left px-5 py-3">Plan</th>
                    <th class="text-left px-5 py-3">Status</th>
                    <th class="text-left px-5 py-3">Created</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-dark">
                @forelse ($teams as $team)
                    <tr class="hover:bg-white/5 transition-colors {{ $team->trashed() ? 'opacity-50' : '' }}">
                        <td class="px-5 py-3">
                            <a href="{{ route('super-admin.teams.show', $team) }}"
                               class="font-medium hover:text-primary transition-colors">{{ $team->name }}</a>
                            <p class="text-xs text-slate-500">{{ $team->slug }}</p>
                        </td>
                        <td class="px-5 py-3 text-slate-400">{{ $team->owner?->name ?? '—' }}</td>
                        <td class="px-5 py-3">{{ $team->members_count }}</td>
                        <td class="px-5 py-3">
                            <x-plan-badge :plan="$team->subscription?->plan" :status="$team->subscription?->status" />
                        </td>
                        <td class="px-5 py-3">
                            @if ($team->trashed())
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-500/10 text-red-400">Deleted</span>
                            @elseif ($team->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-400">Active</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-slate-500/10 text-slate-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-500">{{ $team->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if ($team->trashed())
                                    <form method="POST" action="{{ route('super-admin.teams.restore', $team->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs text-emerald-400 hover:underline">Restore</button>
                                    </form>
                                @else
                                    <a href="{{ route('super-admin.teams.edit', $team) }}"
                                       class="text-slate-400 hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined !text-base">edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('super-admin.teams.destroy', $team) }}"
                                          onsubmit="return confirm('Delete team {{ addslashes($team->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-red-400 transition-colors">
                                            <span class="material-symbols-outlined !text-base">delete</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">No teams found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($teams->hasPages())
            <div class="px-5 py-4 border-t border-border-dark">{{ $teams->links() }}</div>
        @endif
    </div>
</div>
@endsection
