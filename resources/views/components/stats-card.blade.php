@props(['value', 'label', 'icon', 'color' => 'primary', 'trend' => null, 'trendLabel' => null])

@php
    $colors = [
        'primary' => 'bg-primary/10 text-primary',
        'green'   => 'bg-emerald-500/10 text-emerald-400',
        'blue'    => 'bg-blue-500/10 text-blue-400',
        'amber'   => 'bg-amber-500/10 text-amber-400',
        'red'     => 'bg-red-500/10 text-red-400',
    ];
    $iconBg = $colors[$color] ?? $colors['primary'];
@endphp

<div class="glass-card rounded-xl p-5">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider font-medium">{{ $label }}</p>
            <p class="text-2xl font-bold mt-1 text-slate-900 dark:text-white">{{ $value }}</p>
            @if ($trend !== null)
                <p class="text-xs mt-1 {{ $trend >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                    <span class="material-symbols-outlined !text-sm align-middle">
                        {{ $trend >= 0 ? 'trending_up' : 'trending_down' }}
                    </span>
                    {{ $trendLabel ?? ($trend >= 0 ? "+{$trend}%" : "{$trend}%") }}
                </p>
            @endif
        </div>
        <div class="size-10 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined">{{ $icon }}</span>
        </div>
    </div>
</div>
