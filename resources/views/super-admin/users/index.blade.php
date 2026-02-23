@extends('layouts.super-admin')
@section('title', 'Users')
@section('page-title', 'Users')

@section('header-actions')
    <a href="{{ route('super-admin.users.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors neon-glow">
        <span class="material-symbols-outlined !text-base">person_add</span>
        New User
    </a>
@endsection

@section('content')
<div class="space-y-4">

    {{-- Filters --}}
    <form method="GET" class="glass-card rounded-xl p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-xs text-slate-400 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Name or email…"
                   class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary" />
        </div>
        <div class="min-w-36">
            <label class="block text-xs text-slate-400 mb-1">Role</label>
            <select name="role" class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
                <option value="">All Roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(request('role') === $role->name)>
                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                    </option>
                @endforeach
            </select>
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
        <div class="min-w-40">
            <label class="block text-xs text-slate-400 mb-1">Plan</label>
            <select name="plan_id" class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
                <option value="">All Plans</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" @selected(request('plan_id') == $plan->id)>{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg transition-colors">Filter</button>
            <a href="{{ route('super-admin.users.index') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-sm rounded-lg transition-colors">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="border-b border-border-dark">
                <tr class="text-xs text-slate-400 uppercase tracking-wider">
                    <th class="text-left px-5 py-3">User</th>
                    <th class="text-left px-5 py-3">Role</th>
                    <th class="text-left px-5 py-3">Plan</th>
                    <th class="text-left px-5 py-3">Status</th>
                    <th class="text-left px-5 py-3">Joined</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-dark">
                @forelse ($users as $user)
                    <tr class="hover:bg-white/5 transition-colors {{ $user->trashed() ? 'opacity-50' : '' }}">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-full bg-primary/20 flex items-center justify-center shrink-0 text-primary font-semibold text-xs">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('super-admin.users.show', $user) }}"
                                       class="font-medium hover:text-primary transition-colors">{{ $user->name }}</a>
                                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-primary/10 text-primary">
                                {{ $user->roles->first()?->name ?? '–' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <x-plan-badge :plan="$user->subscription?->plan" :status="$user->subscription?->status" />
                        </td>
                        <td class="px-5 py-3">
                            @if ($user->trashed())
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-500/10 text-red-400">Deleted</span>
                            @elseif ($user->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-400">Active</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-slate-500/10 text-slate-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if ($user->trashed())
                                    <form method="POST" action="{{ route('super-admin.users.restore', $user->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs text-emerald-400 hover:text-emerald-300">Restore</button>
                                    </form>
                                @else
                                    <a href="{{ route('super-admin.users.edit', $user) }}"
                                       class="text-slate-400 hover:text-primary transition-colors" title="Edit">
                                        <span class="material-symbols-outlined !text-base">edit</span>
                                    </a>
                                    @unless ($user->hasRole('super_admin'))
                                        <form method="POST" action="{{ route('super-admin.impersonate', $user) }}">
                                            @csrf
                                            <button type="submit" class="text-slate-400 hover:text-amber-400 transition-colors" title="Impersonate">
                                                <span class="material-symbols-outlined !text-base">visibility</span>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('super-admin.users.destroy', $user) }}"
                                              onsubmit="return confirm('Delete {{ addslashes($user->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-red-400 transition-colors" title="Delete">
                                                <span class="material-symbols-outlined !text-base">delete</span>
                                            </button>
                                        </form>
                                    @endunless
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div class="px-5 py-4 border-t border-border-dark">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
