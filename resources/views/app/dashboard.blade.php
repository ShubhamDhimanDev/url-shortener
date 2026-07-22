@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- ── Headline stats ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stats-card
            label="Total Links"
            :value="number_format($totalLinks)"
            icon="link"
            color="primary" />

        <x-stats-card
            label="Active Links"
            :value="number_format($activeLinks)"
            icon="check_circle"
            color="green" />

        <x-stats-card
            label="Clicks Today"
            :value="number_format($clicksToday)"
            icon="ads_click"
            color="blue" />

        <x-stats-card
            label="Clicks This Month"
            :value="number_format($clicksThisMonth)"
            icon="bar_chart"
            color="amber" />
    </div>

    {{-- ── Main content grid ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Clicks chart (last 14 days) --}}
        <div class="lg:col-span-2 glass-card rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Clicks — Last 14 Days</h3>
                <a href="{{ route('app.links.index') }}" class="text-xs text-primary hover:underline">View all links →</a>
            </div>
            <div class="h-40 flex items-end gap-1">
                @php $maxClicks = max($clicksChart->values()->toArray() ?: [1]); @endphp
                @foreach ($clicksChart as $date => $count)
                    @php $pct = $maxClicks > 0 ? ($count / $maxClicks * 100) : 0; @endphp
                    <div class="flex-1 flex flex-col items-center gap-0.5 group relative">
                        <div class="absolute bottom-full mb-1 bg-slate-900 text-white text-[10px] px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-10">
                            {{ $date }}: {{ $count }}
                        </div>
                        <div class="w-full bg-primary/60 hover:bg-primary rounded-sm transition-all"
                             style="height: {{ max($pct, 2) }}%">
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                <span>{{ $clicksChart->keys()->first() }}</span>
                <span>{{ $clicksChart->keys()->last() }}</span>
            </div>
        </div>

        {{-- Plan usage --}}
        <div class="glass-card rounded-xl p-5 flex flex-col gap-4">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Plan Usage</h3>

            @if($activePlan)
                <div class="flex items-center gap-3">
                    <x-plan-badge :plan="$activePlan" />
                    @if($subscription && $subscription->onTrial())
                        <span class="text-xs text-amber-500">Trial ends {{ $subscription->trial_ends_at->diffForHumans() }}</span>
                    @endif
                </div>
            @else
                <p class="text-xs text-slate-400">No active plan.</p>
            @endif

            {{-- Links quota --}}
            <div>
                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mb-1.5">
                    <span>Links this month</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">
                        {{ $linksUsed }}
                        @if($linksLimit !== null && $linksLimit !== '-1')
                            / {{ $linksLimit }}
                        @else
                            / ∞
                        @endif
                    </span>
                </div>
                @if($linksLimit && $linksLimit !== '-1' && (int) $linksLimit > 0)
                    @php $pct = min(round($linksUsed / max((int) $linksLimit, 1) * 100), 100); @endphp
                    <div class="h-2 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 @if($pct >= 90) bg-red-500 @elseif($pct >= 70) bg-amber-500 @else bg-primary @endif"
                             style="width: {{ $pct }}%">
                        </div>
                    </div>
                    @if($linksRemaining !== null && $linksRemaining <= 5)
                        <p class="text-[10px] text-red-500 mt-1">Only {{ $linksRemaining }} links remaining this month!</p>
                    @endif
                @else
                    <div class="h-2 bg-primary/20 rounded-full">
                        <div class="h-full w-full bg-primary/40 rounded-full"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Unlimited links on your plan.</p>
                @endif
            </div>

            <a href="{{ route('app.billing.plans') }}"
               class="mt-auto inline-flex items-center gap-1.5 text-xs text-primary hover:underline font-medium">
                <span class="material-symbols-outlined !text-sm">rocket_launch</span>
                Upgrade plan
            </a>
        </div>
    </div>

    {{-- ── Recent links ─────────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Recent Links</h3>
            <a href="{{ route('app.links.create') }}"
               class="inline-flex items-center gap-1 text-xs text-primary hover:underline font-medium">
                <span class="material-symbols-outlined !text-sm">add_circle</span>
                Create link
            </a>
        </div>

        @forelse ($recentLinks as $link)
            <x-link-card :link="$link" />
            @if(! $loop->last) <div class="my-2 border-t border-slate-200 dark:border-border-dark"></div> @endif
        @empty
            <div class="text-center py-12">
                <span class="material-symbols-outlined !text-4xl text-slate-300 dark:text-slate-700">link_off</span>
                <p class="text-sm text-slate-400 mt-2">No links yet. Create your first short link!</p>
                <a href="{{ route('app.links.create') }}"
                   class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
                    <span class="material-symbols-outlined !text-base">add</span>
                    Create Link
                </a>
            </div>
        @endforelse

        @if($recentLinks->isNotEmpty())
            <div class="mt-4 flex justify-end">
                <a href="{{ route('app.links.index') }}" class="text-xs text-primary hover:underline">View all links →</a>
            </div>
        @endif
    </div>

</div>
@endsection
