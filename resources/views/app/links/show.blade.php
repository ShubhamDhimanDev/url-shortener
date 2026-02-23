@extends('layouts.app')
@section('title', $link->title ?: $link->short_code)
@section('page-title', $link->title ?: $link->short_code)

@section('content')
<div class="space-y-6">

    {{-- ── Link header --}}
    <div class="glass-card rounded-xl p-6 flex flex-wrap gap-4 items-start">
        {{-- Favicon --}}
        <div class="size-14 rounded-xl bg-slate-200 dark:bg-slate-800 flex items-center justify-center shrink-0 overflow-hidden">
            <img src="https://www.google.com/s2/favicons?domain={{ parse_url($link->destination_url, PHP_URL_HOST) }}&sz=64"
                 class="size-10 object-contain"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" alt="">
            <span class="material-symbols-outlined text-slate-400 !text-2xl hidden">link</span>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                    {{ $link->title ?: $link->short_code }}
                </h1>
                @if($link->is_active)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-green-100 dark:bg-green-900/30 text-green-600">
                        <span class="size-1.5 rounded-full bg-green-500"></span> Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-slate-200 dark:bg-slate-700 text-slate-500">
                        <span class="size-1.5 rounded-full bg-slate-400"></span> Inactive
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2 mt-1">
                <a href="{{ $link->short_url }}" target="_blank" class="text-primary font-mono text-sm hover:underline">{{ $link->short_url }}</a>
                <button onclick="navigator.clipboard.writeText('{{ $link->short_url }}')"
                        class="text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined !text-base">content_copy</span>
                </button>
            </div>

            <p class="text-xs text-slate-400 mt-1 truncate" title="{{ $link->destination_url }}">→ {{ $link->destination_url }}</p>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('app.links.edit', $link->ulid) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-slate-200 dark:border-border-dark text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary rounded-lg transition">
                <span class="material-symbols-outlined !text-sm">edit</span> Edit
            </a>
            <a href="{{ route('app.analytics.show', $link->ulid) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-slate-200 dark:border-border-dark text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary rounded-lg transition">
                <span class="material-symbols-outlined !text-sm">bar_chart</span> Analytics
            </a>
        </div>
    </div>

    {{-- ── Stats summary ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stats-card label="Total Clicks"  :value="number_format($summary['total_clicks'])"  icon="ads_click"   color="primary" />
        <x-stats-card label="Unique Clicks" :value="number_format($summary['unique_clicks'])" icon="person"      color="blue" />
        <x-stats-card label="Top Country"   :value="$summary['top_countries'][0]['country'] ?? '—'" icon="public" color="green" />
        <x-stats-card label="Top Device"    :value="ucfirst($summary['top_devices'][0]['device_type'] ?? '—')" icon="devices" color="amber" />
    </div>

    {{-- ── Clicks over time ────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Clicks Over Time</h3>
            <a href="{{ route('app.analytics.show', $link->ulid) }}"
               class="text-xs text-primary hover:underline">Full analytics →</a>
        </div>

        @if(! empty($summary['clicks_over_time']))
            @php $maxClicks = max(array_column($summary['clicks_over_time'], 'count') ?: [1]); @endphp
            <div class="h-32 flex items-end gap-1">
                @foreach ($summary['clicks_over_time'] as $item)
                    @php $pct = $maxClicks > 0 ? ($item['count'] / $maxClicks * 100) : 0; @endphp
                    <div class="flex-1 flex flex-col items-center gap-0.5 group relative">
                        <div class="absolute bottom-full mb-1 bg-slate-900 text-white text-[10px] px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-10">
                            {{ $item['date'] }}: {{ $item['count'] }}
                        </div>
                        <div class="w-full bg-primary/60 hover:bg-primary rounded-sm transition-all"
                             style="height: {{ max($pct, 2) }}%">
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-400 text-center py-8">No click data yet.</p>
        @endif
    </div>

    {{-- ── Details grid ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Countries --}}
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Top Countries</h3>
            @forelse ($summary['top_countries'] as $item)
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-sm text-slate-600 dark:text-slate-300">{{ $item['country'] ?? 'Unknown' }}</span>
                    <span class="text-sm font-semibold">{{ number_format($item['count']) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">No data yet.</p>
            @endforelse
        </div>

        {{-- Referrers --}}
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Top Referrers</h3>
            @forelse ($summary['top_referrers'] as $item)
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-sm text-slate-600 dark:text-slate-300 truncate max-w-[200px]">{{ $item['referrer_domain'] ?? 'Direct' }}</span>
                    <span class="text-sm font-semibold">{{ number_format($item['count']) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">No data yet.</p>
            @endforelse
        </div>

        {{-- Devices  --}}
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Devices</h3>
            @forelse ($summary['top_devices'] as $item)
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-sm text-slate-600 dark:text-slate-300 capitalize">{{ $item['device_type'] }}</span>
                    <span class="text-sm font-semibold">{{ number_format($item['count']) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">No data yet.</p>
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
                <p class="text-sm text-slate-400">No data yet.</p>
            @endforelse
        </div>
    </div>

    {{-- ── QR Code section ─────────────────────────────────────────────── --}}
    <x-feature-gate feature="qrcode">
        <div class="glass-card rounded-xl p-5 flex flex-wrap gap-6 items-start">
            <div>
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">QR Code</h3>
                @if($link->qrCode)
                    <div class="p-3 bg-white rounded-xl inline-block">
                        <img src="{{ Storage::url($link->qrCode->file_path) }}"
                             class="size-28 object-contain"
                             alt="QR Code for {{ $link->short_url }}" />
                    </div>
                @else
                    <p class="text-xs text-slate-400">Not generated yet.</p>
                @endif
            </div>
            <div class="flex flex-col gap-2 pt-1">
                <form method="POST" action="{{ route('app.qrcodes.generate', $link->ulid) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
                        <span class="material-symbols-outlined !text-sm">qr_code_2</span>
                        {{ $link->qrCode ? 'Regenerate' : 'Generate' }} QR
                    </button>
                </form>
                @if($link->qrCode)
                    <a href="{{ route('app.qrcodes.download', $link->ulid) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-200 dark:border-border-dark text-sm text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary rounded-lg transition">
                        <span class="material-symbols-outlined !text-sm">download</span> Download
                    </a>
                    <a href="{{ route('app.qrcodes.show', $link->ulid) }}"
                       class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-primary transition">
                        Customise colours →
                    </a>
                @endif
            </div>
        </div>
    </x-feature-gate>

    {{-- ── Link metadata ────────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-5">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Link Details</h3>
        <dl class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div>
                <dt class="text-xs text-slate-400 mb-0.5">Short Code</dt>
                <dd class="font-mono font-semibold">{{ $link->short_code }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-400 mb-0.5">Created</dt>
                <dd>{{ $link->created_at->format('M j, Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-400 mb-0.5">Expires</dt>
                <dd>{{ $link->expires_at ? $link->expires_at->format('M j, Y H:i') : 'Never' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-400 mb-0.5">Password Protected</dt>
                <dd>{{ $link->is_password_protected ? 'Yes' : 'No' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-400 mb-0.5">Bot Protection</dt>
                <dd>{{ $link->is_bot_protection_enabled ? 'Enabled' : 'Disabled' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-400 mb-0.5">Custom Domain</dt>
                <dd>{{ $link->domain?->domain ?? 'None (default)' }}</dd>
            </div>
        </dl>
    </div>

</div>
@endsection
