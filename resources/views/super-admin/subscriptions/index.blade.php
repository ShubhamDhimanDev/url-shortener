@extends('layouts.super-admin')
@section('title', 'Subscriptions')
@section('page-title', 'Subscriptions')

@section('content')
<div class="space-y-4">

    {{-- Filters --}}
    <form method="GET" class="glass-card rounded-xl p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-xs text-slate-400 mb-1">Search Gateway ID</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="sub_xxx…"
                   class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary" />
        </div>
        <div class="min-w-36">
            <label class="block text-xs text-slate-400 mb-1">Status</label>
            <select name="status" class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
                <option value="">All</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
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
        <div class="min-w-36">
            <label class="block text-xs text-slate-400 mb-1">Gateway</label>
            <select name="gateway" class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
                <option value="">All</option>
                @foreach ($gateways as $gw)
                    <option value="{{ $gw }}" @selected(request('gateway') === $gw)>{{ ucfirst($gw) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">Filter</button>
            <a href="{{ route('super-admin.subscriptions.index') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-sm rounded-lg">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="border-b border-border-dark">
                <tr class="text-xs text-slate-400 uppercase tracking-wider">
                    <th class="text-left px-5 py-3">Subscriber</th>
                    <th class="text-left px-5 py-3">Plan</th>
                    <th class="text-left px-5 py-3">Status</th>
                    <th class="text-left px-5 py-3">Gateway</th>
                    <th class="text-left px-5 py-3">Period End</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-dark">
                @forelse ($subscriptions as $sub)
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-5 py-3">
                            <div class="font-medium">{{ $sub->subscribable?->name ?? '—' }}</div>
                            <div class="text-xs text-slate-500">{{ class_basename($sub->subscribable_type) }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <x-plan-badge :plan="$sub->plan" />
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $statusColors = [
                                    'active'   => 'bg-emerald-500/10 text-emerald-400',
                                    'trialing' => 'bg-blue-500/10 text-blue-400',
                                    'past_due' => 'bg-amber-500/10 text-amber-400',
                                    'cancelled'=> 'bg-red-500/10 text-red-400',
                                    'expired'  => 'bg-slate-500/10 text-slate-400',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $statusColors[$sub->status] ?? 'bg-slate-500/10 text-slate-400' }}">
                                {{ ucfirst(str_replace('_', ' ', $sub->status)) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 capitalize text-slate-400">{{ $sub->gateway }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $sub->current_period_end?->format('d M Y') ?? '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('super-admin.subscriptions.show', $sub) }}"
                               class="text-primary hover:underline text-xs">View →</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">No subscriptions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($subscriptions->hasPages())
            <div class="px-5 py-4 border-t border-border-dark">{{ $subscriptions->links() }}</div>
        @endif
    </div>
</div>
@endsection
