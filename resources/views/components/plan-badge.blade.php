@props(['plan' => null, 'status' => null])

@php
    $planName = $plan ? $plan->name : 'Free';
    $bgMap = [
        'Free'     => 'bg-slate-500/10 text-slate-400',
        'Pro'      => 'bg-primary/10 text-primary',
        'Business' => 'bg-amber-500/10 text-amber-400',
    ];
    $bg = $bgMap[$planName] ?? 'bg-slate-500/10 text-slate-400';

    $statusBgMap = [
        'active'   => 'bg-emerald-500/10 text-emerald-400',
        'trialing' => 'bg-blue-500/10 text-blue-400',
        'past_due' => 'bg-amber-500/10 text-amber-400',
        'cancelled'=> 'bg-red-500/10 text-red-400',
        'expired'  => 'bg-slate-500/10 text-slate-400',
    ];
    $statusBg = $statusBgMap[$status] ?? 'bg-slate-500/10 text-slate-400';
@endphp

<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $bg }}">
    {{ $planName }}
</span>
@if ($status)
    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBg }} ml-1">
        {{ ucfirst(str_replace('_', ' ', $status)) }}
    </span>
@endif
