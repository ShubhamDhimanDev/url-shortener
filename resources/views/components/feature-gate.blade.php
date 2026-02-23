@props(['feature', 'label' => null])

@php
    $user = auth()->user();
    $entity = session('active_team_id')
        ? optional($user->teamMemberships()->where('team_id', session('active_team_id'))->first())->team ?? $user
        : $user;

    $service = \App\Services\FeatureService::for($entity);
    $hasFeature = $service->can($feature);
@endphp

@if($hasFeature)
    {{ $slot }}
@else
    <div class="relative rounded-xl border border-dashed border-slate-300 dark:border-border-dark bg-slate-50 dark:bg-card-dark/50 p-6 flex flex-col items-center justify-center text-center gap-3">
        <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center">
            <span class="material-symbols-outlined text-primary">lock</span>
        </div>
        <div>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                {{ $label ?? ucwords(str_replace('_', ' ', $feature)) }} is not available on your plan
            </p>
            <p class="text-xs text-slate-400 mt-1">Upgrade your plan to unlock this feature.</p>
        </div>
        <a href="{{ route('app.billing.plans') }}"
           class="mt-1 inline-flex items-center gap-1.5 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-xs font-semibold rounded-lg transition-colors">
            <span class="material-symbols-outlined !text-sm">rocket_launch</span>
            Upgrade Plan
        </a>
    </div>
@endif
