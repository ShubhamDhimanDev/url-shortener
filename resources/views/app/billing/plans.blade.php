@extends('layouts.app')
@section('title', 'Choose a Plan')
@section('page-title', 'Plans')

@section('content')
<div class="max-w-5xl">

    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
        You are currently on the <strong class="text-primary">{{ $currentPlan?->name ?? 'Free' }}</strong> plan.
        Billing is applied to <strong class="text-slate-700 dark:text-slate-200">{{ $entity instanceof \App\Models\Team ? $entity->name : 'your personal account' }}</strong>.
    </p>

    {{-- ── Plan grid ────────────────────────────────────────────────────── --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($plans as $plan)
            @php
                $isCurrent = $currentPlan?->id === $plan->id;
                $isPro     = str_contains(strtolower($plan->name), 'pro');
            @endphp
            <div class="glass-card rounded-xl p-6 flex flex-col relative
                        @if($isCurrent) ring-2 ring-primary @endif
                        @if($isPro) shadow-lg shadow-primary/10 @endif">

                @if($isCurrent)
                    <span class="absolute top-3 right-3 text-xs px-2 py-0.5 bg-primary/10 text-primary rounded-full font-medium">
                        Current
                    </span>
                @endif

                <div class="mb-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-primary mb-1">{{ $plan->name }}</p>
                    <div class="flex items-end gap-1 mb-2">
                        <span class="text-3xl font-extrabold text-slate-900 dark:text-white">
                            {{ $plan->price_monthly > 0 ? '₹' . number_format($plan->price_monthly, 0) : 'Free' }}
                        </span>
                        @if($plan->price_monthly > 0)
                            <span class="text-slate-400 text-sm mb-1">/mo</span>
                        @endif
                    </div>
                    @if($plan->description)
                        <p class="text-xs text-slate-400">{{ $plan->description }}</p>
                    @endif
                </div>

                <ul class="space-y-2.5 flex-1 mb-6">
                    @foreach($plan->features as $feature)
                        <li class="flex items-start gap-2 text-xs text-slate-600 dark:text-slate-300">
                            <span class="material-symbols-outlined !text-sm text-green-400 mt-0.5 shrink-0">check_circle</span>
                            <span>{{ $feature->description ?? $feature->feature_key }}</span>
                        </li>
                    @endforeach
                </ul>

                @if(!$isCurrent)
                    <form method="POST" action="{{ route('app.billing.subscribe') }}">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}" />
                        <button type="submit"
                                class="w-full py-2.5 rounded-lg text-sm font-semibold text-white transition
                                       @if($isPro) bg-primary hover:bg-primary/90
                                       @else bg-slate-600 hover:bg-slate-500 @endif">
                            {{ $plan->price > 0 ? 'Upgrade to ' . $plan->name : 'Switch to ' . $plan->name }}
                        </button>
                    </form>
                @else
                    <div class="w-full py-2.5 rounded-lg text-center text-sm font-semibold
                                bg-primary/10 text-primary cursor-default">
                        Current Plan
                    </div>
                @endif

            </div>
        @endforeach
    </div>

    <p class="text-center text-xs text-slate-400 mt-6">
        Prices shown in Indian Rupees (INR). Cancel any time.
    </p>
</div>
@endsection
