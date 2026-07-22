@extends('layouts.super-admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">

    {{-- ── Headline stats ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stats-card
            label="Total Users"
            :value="number_format($totalUsers)"
            icon="group"
            color="primary" />

        <x-stats-card
            label="Short Links"
            :value="number_format($totalLinks)"
            icon="link"
            color="blue" />

        <x-stats-card
            label="Clicks Today"
            :value="number_format($clicksToday)"
            icon="ads_click"
            color="green" />

        <x-stats-card
            label="MRR"
            :value="'₹ ' . number_format($mrr, 2)"
            icon="payments"
            color="amber" />
    </div>

    {{-- ── Revenue & Subscription breakdown ───────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Signups chart --}}
        <div class="lg:col-span-2 glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold mb-4 text-slate-700 dark:text-slate-200">New Signups — Last 30 Days</h3>
            <div class="h-40 flex items-end gap-0.5">
                @php
                    $maxSignups = max($signupChart->values()->toArray() ?: [1]);
                @endphp
                @foreach ($signupChart as $date => $count)
                    @php $pct = $maxSignups > 0 ? ($count / $maxSignups * 100) : 0; @endphp
                    <div class="flex-1 flex flex-col items-center gap-0.5 group relative">
                        <div class="w-full bg-primary/60 hover:bg-primary rounded-t transition-all"
                             style="height: {{ max($pct, 2) }}%"
                             title="{{ $date }}: {{ $count }} signups">
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                <span>{{ $signupChart->keys()->first() }}</span>
                <span>{{ $signupChart->keys()->last() }}</span>
            </div>
        </div>

        {{-- Subscriptions by plan --}}
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold mb-4 text-slate-700 dark:text-slate-200">Active Subscriptions</h3>
            <div class="space-y-3">
                @forelse ($subscriptionsByPlan as $planName => $count)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600 dark:text-slate-300">{{ $planName }}</span>
                        <span class="text-sm font-semibold text-white">{{ $count }}</span>
                    </div>
                    <div class="h-1.5 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                        @php $total = $subscriptionsByPlan->sum(); @endphp
                        <div class="h-full bg-primary rounded-full"
                             style="width: {{ $total > 0 ? round($count/$total*100) : 0 }}%">
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No active subscriptions.</p>
                @endforelse
            </div>
            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-border-dark">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">Revenue this month</span>
                    <span class="font-semibold text-emerald-400">₹{{ number_format($revenueThisMonth, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Click activity ───────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-5">
        <h3 class="text-sm font-semibold mb-4 text-slate-700 dark:text-slate-200">Click Activity — Last 30 Days</h3>
        <div class="h-32 flex items-end gap-0.5">
            @php $maxClicks = max($clicksChart->values()->toArray() ?: [1]); @endphp
            @foreach ($clicksChart as $date => $count)
                @php $pct = $maxClicks > 0 ? ($count / $maxClicks * 100) : 0; @endphp
                <div class="flex-1 bg-accent-pink/50 hover:bg-accent-pink rounded-t transition-all"
                     style="height: {{ max($pct, 1) }}%"
                     title="{{ $date }}: {{ $count }} clicks">
                </div>
            @endforeach
        </div>
        <div class="flex justify-between text-[10px] text-slate-400 mt-1">
            <span>{{ $clicksChart->keys()->first() }}</span>
            <span>{{ $clicksChart->keys()->last() }}</span>
        </div>
    </div>

    {{-- ── Recent signups table ─────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Recent Users</h3>
            <a href="{{ route('super-admin.users.index') }}"
               class="text-xs text-primary hover:underline">View all →</a>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-border-dark text-xs text-slate-400 uppercase tracking-wider">
                    <th class="text-left py-2 pr-4">Name</th>
                    <th class="text-left py-2 pr-4">Email</th>
                    <th class="text-left py-2 pr-4">Role</th>
                    <th class="text-left py-2">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                @foreach ($recentUsers as $user)
                    <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                        <td class="py-3 pr-4">
                            <a href="{{ route('super-admin.users.show', $user) }}"
                               class="font-medium hover:text-primary transition-colors">{{ $user->name }}</a>
                        </td>
                        <td class="py-3 pr-4 text-slate-500">{{ $user->email }}</td>
                        <td class="py-3 pr-4">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-primary/10 text-primary">
                                {{ $user->roles->first()?->name ?? 'No role' }}
                            </span>
                        </td>
                        <td class="py-3 text-slate-500">{{ $user->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
