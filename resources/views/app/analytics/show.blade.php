@extends('layouts.app')
@section('title', 'Analytics — ' . ($link->title ?: $link->short_code))
@section('page-title', 'Link Analytics')

@section('content')
<div class="space-y-6">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('app.links.show', $link->ulid) }}"
               class="inline-flex items-center gap-1 text-xs text-slate-400 hover:text-primary mb-1">
                <span class="material-symbols-outlined !text-sm">arrow_back</span> Back to link
            </a>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">{{ $link->title ?: $link->short_code }}</h2>
            <a href="{{ $link->short_url }}" target="_blank" class="text-xs text-primary">{{ $link->short_url }}</a>
        </div>

        {{-- Date range picker --}}
        <form method="GET" action="{{ route('app.analytics.show', $link->ulid) }}"
              class="flex items-end gap-2">
            <div>
                <label class="block text-xs text-slate-400 mb-1">From</label>
                <input type="date" name="from" value="{{ $from->toDateString() }}"
                       class="text-sm bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary" />
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">To</label>
                <input type="date" name="to" value="{{ $to->toDateString() }}"
                       class="text-sm bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary" />
            </div>
            <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
                Apply
            </button>
        </form>
    </div>

    {{-- ── Stats grid ───────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stats-card label="Total Clicks"  :value="number_format($summary['total_clicks'])"  icon="ads_click"   color="primary" />
        <x-stats-card label="Unique Clicks" :value="number_format($summary['unique_clicks'])" icon="person"      color="blue" />
        <x-stats-card label="Bot Clicks"    :value="number_format($summary['bot_clicks'] ?? 0)" icon="smart_toy" color="amber" />
        <x-stats-card label="Human Clicks"  :value="number_format($summary['total_clicks'] - ($summary['bot_clicks'] ?? 0))" icon="group" color="green" />
    </div>

    {{-- ── Clicks over time ─────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-5">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Clicks Over Time</h3>
        @if(! empty($summary['clicks_over_time']))
            @php $maxC = max(array_column($summary['clicks_over_time'], 'count') ?: [1]); @endphp
            <div class="h-48 flex items-end gap-0.5">
                @foreach ($summary['clicks_over_time'] as $item)
                    @php $pct = $maxC > 0 ? ($item['count'] / $maxC * 100) : 0; @endphp
                    <div class="flex-1 flex flex-col items-center group relative min-w-0">
                        <div class="absolute bottom-full mb-1 bg-slate-900 text-white text-[10px] px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap z-10 transition">
                            {{ $item['date'] }}: {{ $item['count'] }}
                        </div>
                        <div class="w-full bg-primary/60 hover:bg-primary rounded-t transition-all"
                             style="height: {{ max($pct, 1) }}%">
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                <span>{{ $summary['clicks_over_time'][0]['date'] ?? '' }}</span>
                <span>{{ $summary['clicks_over_time'][array_key_last($summary['clicks_over_time'])]['date'] ?? '' }}</span>
            </div>
        @else
            <p class="text-sm text-slate-400 text-center py-8">No click data in the selected period.</p>
        @endif
    </div>

    {{-- ── Breakdown grid ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        {{-- Countries --}}
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Top Countries</h3>
            @php $maxCountry = max(array_column($summary['top_countries'] ?: [['count'=>1]], 'count')); @endphp
            @forelse ($summary['top_countries'] as $item)
                <div class="mb-2">
                    <div class="flex justify-between text-xs text-slate-600 dark:text-slate-300 mb-0.5">
                        <span>{{ $item['country'] ?? 'Unknown' }}</span>
                        <span class="font-semibold">{{ number_format($item['count']) }}</span>
                    </div>
                    <div class="h-1.5 bg-slate-200 dark:bg-slate-800 rounded-full">
                        <div class="h-full bg-primary rounded-full" style="width: {{ $maxCountry > 0 ? round($item['count']/$maxCountry*100) : 0 }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400">No data yet.</p>
            @endforelse
        </div>

        {{-- Devices --}}
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Devices</h3>
            @php $totalDev = array_sum(array_column($summary['top_devices'] ?: [], 'count')) ?: 1; @endphp
            @forelse ($summary['top_devices'] as $item)
                @php $pct = round($item['count'] / $totalDev * 100); @endphp
                <div class="mb-2">
                    <div class="flex justify-between text-xs text-slate-600 dark:text-slate-300 mb-0.5">
                        <span class="capitalize">{{ $item['device_type'] }}</span>
                        <span class="font-semibold">{{ $pct }}%</span>
                    </div>
                    <div class="h-1.5 bg-slate-200 dark:bg-slate-800 rounded-full">
                        <div class="h-full rounded-full @switch($item['device_type'])
                            @case('mobile')  bg-blue-500   @break
                            @case('desktop') bg-primary    @break
                            @case('tablet')  bg-amber-500  @break
                            @default         bg-slate-400
                            @endswitch" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400">No data yet.</p>
            @endforelse
        </div>

        {{-- Browsers --}}
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Browsers</h3>
            @forelse ($summary['top_browsers'] as $item)
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-sm text-slate-600 dark:text-slate-300">{{ $item['browser'] ?? 'Unknown' }}</span>
                    <span class="text-sm font-semibold">{{ number_format($item['count']) }}</span>
                </div>
            @empty
                <p class="text-xs text-slate-400">No data yet.</p>
            @endforelse
        </div>

        {{-- OS --}}
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Operating Systems</h3>
            @forelse ($summary['top_os'] as $item)
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-sm text-slate-600 dark:text-slate-300">{{ $item['os'] ?? 'Unknown' }}</span>
                    <span class="text-sm font-semibold">{{ number_format($item['count']) }}</span>
                </div>
            @empty
                <p class="text-xs text-slate-400">No data yet.</p>
            @endforelse
        </div>

        {{-- Referrers --}}
        <div class="glass-card rounded-xl p-5 md:col-span-2">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Top Referrers</h3>
            @php $maxRef = max(array_column($summary['top_referrers'] ?: [['count'=>1]], 'count')); @endphp
            @forelse ($summary['top_referrers'] as $item)
                <div class="mb-2">
                    <div class="flex justify-between text-xs text-slate-600 dark:text-slate-300 mb-0.5">
                        <span class="truncate max-w-[250px]">{{ $item['referrer_domain'] ?? 'Direct / None' }}</span>
                        <span class="font-semibold ml-4">{{ number_format($item['count']) }}</span>
                    </div>
                    <div class="h-1.5 bg-slate-200 dark:bg-slate-800 rounded-full">
                        <div class="h-full bg-accent-pink rounded-full" style="width: {{ $maxRef > 0 ? round($item['count']/$maxRef*100) : 0 }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400">No data yet.</p>
            @endforelse
        </div>

    </div>

    {{-- ── UTM Breakdown (if campaign_tracking feature enabled) ───────────── --}}
    <x-feature-gate feature="campaign_tracking">
        @if(! empty($summary['utm_sources']) && collect($summary['utm_sources'])->sum('count') > 0)
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">UTM Campaign Breakdown</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-slate-400 mb-2 font-medium uppercase tracking-wider">Sources</p>
                    @foreach ($summary['utm_sources'] ?? [] as $item)
                        <div class="flex justify-between text-xs py-1">
                            <span class="text-slate-600 dark:text-slate-300">{{ $item['utm_source'] ?: '—' }}</span>
                            <span class="font-semibold">{{ $item['count'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-2 font-medium uppercase tracking-wider">Mediums</p>
                    @foreach ($summary['utm_mediums'] ?? [] as $item)
                        <div class="flex justify-between text-xs py-1">
                            <span class="text-slate-600 dark:text-slate-300">{{ $item['utm_medium'] ?: '—' }}</span>
                            <span class="font-semibold">{{ $item['count'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-2 font-medium uppercase tracking-wider">Campaigns</p>
                    @foreach ($summary['utm_campaigns'] ?? [] as $item)
                        <div class="flex justify-between text-xs py-1">
                            <span class="text-slate-600 dark:text-slate-300">{{ $item['utm_campaign'] ?: '—' }}</span>
                            <span class="font-semibold">{{ $item['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </x-feature-gate>

</div>
@endsection
