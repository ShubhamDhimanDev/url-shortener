@extends('layouts.super-admin')
@section('title', 'Plans')
@section('page-title', 'Plans')

@section('header-actions')
    <a href="{{ route('super-admin.plans.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors neon-glow">
        <span class="material-symbols-outlined !text-base">add</span>
        New Plan
    </a>
@endsection

@section('content')
<div class="space-y-4">
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="border-b border-border-dark">
                <tr class="text-xs text-slate-400 uppercase tracking-wider">
                    <th class="text-left px-5 py-3">Plan</th>
                    <th class="text-left px-5 py-3">Price/mo</th>
                    <th class="text-left px-5 py-3">Price/yr</th>
                    <th class="text-left px-5 py-3">Trial Days</th>
                    <th class="text-left px-5 py-3">Subscribers</th>
                    <th class="text-left px-5 py-3">Flags</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-dark">
                @forelse ($plans as $plan)
                    <tr class="hover:bg-white/5 transition-colors {{ $plan->trashed() ? 'opacity-40' : '' }}">
                        <td class="px-5 py-3">
                            <div class="font-medium">{{ $plan->name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $plan->slug }}</div>
                            @if ($plan->description)
                                <div class="text-xs text-slate-400 mt-0.5 max-w-48 truncate">{{ $plan->description }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if ($plan->price_monthly == 0) Free
                            @else {{ $plan->currency }} {{ number_format($plan->price_monthly, 2) }}
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if ($plan->price_yearly == 0) Free
                            @else {{ $plan->currency }} {{ number_format($plan->price_yearly, 2) }}
                            @endif
                        </td>
                        <td class="px-5 py-3">{{ $plan->trial_days ?: '—' }}</td>
                        <td class="px-5 py-3">{{ number_format($plan->subscriptions_count) }}</td>
                        <td class="px-5 py-3">
                            <div class="flex gap-1 flex-wrap">
                                @if ($plan->is_active)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400">Active</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-500/10 text-slate-400">Inactive</span>
                                @endif
                                @if ($plan->is_public)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-blue-500/10 text-blue-400">Public</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('super-admin.plans.edit', $plan) }}"
                                   class="text-slate-400 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined !text-base">edit</span>
                                </a>
                                <form method="POST" action="{{ route('super-admin.plans.toggle-active', $plan) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-slate-400 hover:text-amber-400 transition-colors"
                                            title="{{ $plan->is_active ? 'Deactivate' : 'Activate' }}">
                                        <span class="material-symbols-outlined !text-base">
                                            {{ $plan->is_active ? 'toggle_on' : 'toggle_off' }}
                                        </span>
                                    </button>
                                </form>
                                @if (! $plan->trashed())
                                    <form method="POST" action="{{ route('super-admin.plans.destroy', $plan) }}"
                                          onsubmit="return confirm('Delete plan {{ addslashes($plan->name) }}?')">
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
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">No plans yet. Create one to get started.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
