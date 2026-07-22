@extends('layouts.super-admin')
@section('title', 'Platform Analytics')
@section('page-title', 'Platform Analytics')

@section('content')
<div class="space-y-6">

    {{-- Date filter --}}
    <form method="GET" class="glass-card rounded-xl p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-slate-400 mb-1">From</label>
            <input type="date" name="from" value="{{ request('from', $from->toDateString()) }}"
                   class="bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
        </div>
        <div>
            <label class="block text-xs text-slate-400 mb-1">To</label>
            <input type="date" name="to" value="{{ request('to', $to->toDateString()) }}"
                   class="bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
        </div>
        <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">Apply</button>
        <div class="flex gap-2 ml-2">
            <a href="{{ route('super-admin.analytics.index', ['from' => now()->subDays(6)->toDateString(), 'to' => now()->toDateString()]) }}"
               class="px-3 py-2 bg-white/5 hover:bg-white/10 text-xs rounded-lg">7D</a>
            <a href="{{ route('super-admin.analytics.index', ['from' => now()->subDays(29)->toDateString(), 'to' => now()->toDateString()]) }}"
               class="px-3 py-2 bg-white/5 hover:bg-white/10 text-xs rounded-lg">30D</a>
            <a href="{{ route('super-admin.analytics.index', ['from' => now()->subDays(89)->toDateString(), 'to' => now()->toDateString()]) }}"
               class="px-3 py-2 bg-white/5 hover:bg-white/10 text-xs rounded-lg">90D</a>
        </div>
    </form>

    {{-- Summary stats --}}
    @if (isset($summary))
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stats-card label="Total Clicks"    :value="number_format($summary['total_clicks'])"   icon="ads_click"    color="primary" />
        <x-stats-card label="Unique Clicks"   :value="number_format($summary['unique_clicks'])"  icon="person"       color="blue" />
        <x-stats-card label="Top Country"     :value="$summary['top_countries'][0]['country'] ?? 'N/A'" icon="language" color="green" />
        <x-stats-card label="Top Device"      :value="ucfirst($summary['top_devices'][0]['device_type'] ?? 'N/A')" icon="devices" color="amber" />
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Top Links --}}
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold mb-4">Top Links by Clicks</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-400 uppercase tracking-wider border-b border-border-dark">
                        <th class="text-left py-2 pr-4">Short Code</th>
                        <th class="text-left py-2 pr-4">Owner</th>
                        <th class="text-right py-2">Clicks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark">
                    @forelse ($topLinks as $link)
                        <tr>
                            <td class="py-2 pr-4 font-mono text-xs text-primary">{{ $link->short_code }}</td>
                            <td class="py-2 pr-4 text-slate-400 text-xs">{{ $link->user?->name ?? '—' }}</td>
                            <td class="py-2 text-right font-semibold">{{ number_format($link->period_clicks) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-slate-400">No data for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Top Countries --}}
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold mb-4">Top Countries</h3>
            @if (isset($summary) && ! empty($summary['top_countries']))
                <div class="space-y-2">
                    @foreach (array_slice($summary['top_countries'], 0, 8) as $item)
                        @php
                            $maxCountry = $summary['top_countries'][0]['count'] ?? 1;
                            $pct = $maxCountry > 0 ? round($item['count'] / $maxCountry * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>{{ $item['country'] ?: 'Unknown' }}</span>
                                <span class="text-slate-400">{{ number_format($item['count']) }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-primary rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400">No geographic data for this period.</p>
            @endif
        </div>

        {{-- Device Breakdown --}}
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold mb-4">Device Breakdown</h3>
            @if (isset($summary) && ! empty($summary['top_devices']))
                <div class="space-y-2">
                    @php $totalDeviceClicks = array_sum(array_column($summary['top_devices'], 'count')); @endphp
                    @foreach ($summary['top_devices'] as $device)
                        @php $pct = $totalDeviceClicks > 0 ? round($device['count'] / $totalDeviceClicks * 100) : 0; @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="capitalize">{{ $device['device_type'] }}</span>
                                <span class="text-slate-400">{{ $pct }}%</span>
                            </div>
                            <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-accent-pink rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400">No device data for this period.</p>
            @endif
        </div>

        {{-- Top Users --}}
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold mb-4">Top Users by Links</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-400 uppercase tracking-wider border-b border-border-dark">
                        <th class="text-left py-2 pr-4">User</th>
                        <th class="text-right py-2">Total Links</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark">
                    @forelse ($topUsers as $user)
                        <tr>
                            <td class="py-2 pr-4">
                                <a href="{{ route('super-admin.users.show', $user) }}"
                                   class="hover:text-primary transition-colors">{{ $user->name }}</a>
                            </td>
                            <td class="py-2 text-right font-semibold">{{ number_format($user->total_links) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="py-4 text-center text-slate-400">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Click timeline --}}
    @if (isset($summary) && ! empty($summary['clicks_over_time']))
    <div class="glass-card rounded-xl p-5">
        <h3 class="text-sm font-semibold mb-4">Clicks Over Time</h3>
        <div class="h-40 flex items-end gap-0.5">
            @php $maxC = max(array_column($summary['clicks_over_time'], 'count') ?: [1]); @endphp
            @foreach ($summary['clicks_over_time'] as $point)
                @php $pct = $maxC > 0 ? ($point['count'] / $maxC * 100) : 0; @endphp
                <div class="flex-1 bg-primary/50 hover:bg-primary rounded-t transition-all"
                     style="height: {{ max($pct, 1) }}%"
                     title="{{ $point['date'] }}: {{ $point['count'] }} clicks">
                </div>
            @endforeach
        </div>
        <div class="flex justify-between text-[10px] text-slate-400 mt-1">
            <span>{{ $from->toDateString() }}</span>
            <span>{{ $to->toDateString() }}</span>
        </div>
    </div>
    @endif

</div>
@endsection
