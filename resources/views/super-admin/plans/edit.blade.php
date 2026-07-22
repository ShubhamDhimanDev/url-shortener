@extends('layouts.super-admin')
@section('title', 'Edit ' . $plan->name)
@section('page-title', 'Edit Plan: ' . $plan->name)

@section('header-actions')
    <a href="{{ route('super-admin.plans.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 bg-white/5 hover:bg-white/10 text-sm rounded-lg transition-colors">← Plans</a>
@endsection

@section('content')
<form method="POST" action="{{ route('super-admin.plans.update', $plan) }}" class="space-y-6">
@csrf
@method('PUT')

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- Plan details --}}
    <div class="lg:col-span-2 glass-card rounded-xl p-6 space-y-4">
        <h3 class="text-sm font-semibold border-b border-border-dark pb-3">Plan Details</h3>

        <div>
            <label class="block text-xs text-slate-400 mb-1.5 font-medium">Plan Name <span class="text-red-400">*</span></label>
            <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                   class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
        </div>

        <div>
            <label class="block text-xs text-slate-400 mb-1.5 font-medium">Description</label>
            <textarea name="description" rows="3"
                      class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">{{ old('description', $plan->description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-slate-400 mb-1.5 font-medium">Monthly Price</label>
                <input type="number" name="price_monthly" value="{{ old('price_monthly', $plan->price_monthly) }}" min="0" step="0.01"
                       class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5 font-medium">Yearly Price</label>
                <input type="number" name="price_yearly" value="{{ old('price_yearly', $plan->price_yearly) }}" min="0" step="0.01"
                       class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-slate-400 mb-1.5 font-medium">Currency</label>
                <input type="text" name="currency" value="{{ old('currency', $plan->currency) }}" maxlength="3"
                       class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5 font-medium">Trial Days</label>
                <input type="number" name="trial_days" value="{{ old('trial_days', $plan->trial_days) }}" min="0"
                       class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
            </div>
        </div>

        <div class="flex items-center gap-6 pt-1">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $plan->is_active))
                       class="rounded border-border-dark bg-black/30 text-primary focus:ring-primary" />
                <span class="text-sm text-slate-300">Active</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_public" value="0">
                <input type="checkbox" name="is_public" value="1"
                       @checked(old('is_public', $plan->is_public))
                       class="rounded border-border-dark bg-black/30 text-primary focus:ring-primary" />
                <span class="text-sm text-slate-300">Public</span>
            </label>
        </div>
    </div>

    {{-- Plan features --}}
    <div class="lg:col-span-3 glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold border-b border-border-dark pb-3 mb-4">Plan Features</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach ($featureKeys as $key)
                @php $currentValue = old("features.{$key}", $featureValues->get($key)); @endphp
                <div>
                    <label class="block text-xs text-slate-400 mb-1 capitalize">
                        {{ str_replace('_', ' ', $key) }}
                    </label>
                    @if ($key === 'analytics_level')
                        <select name="features[{{ $key }}]"
                                class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
                            <option value="none"     @selected($currentValue === 'none')>None</option>
                            <option value="basic"    @selected($currentValue === 'basic')>Basic</option>
                            <option value="advanced" @selected($currentValue === 'advanced')>Advanced</option>
                        </select>
                    @elseif (in_array($key, ['links_per_month', 'custom_domains_count', 'team_members_count']))
                        <input type="number" name="features[{{ $key }}]"
                               value="{{ $currentValue ?? 0 }}" min="0"
                               class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
                    @else
                        <div class="flex items-center gap-2 pt-1">
                            <input type="hidden" name="features[{{ $key }}]" value="0">
                            <input type="checkbox" name="features[{{ $key }}]" value="1"
                                   id="feat_{{ $key }}"
                                   @checked(filter_var($currentValue, FILTER_VALIDATE_BOOLEAN))
                                   class="rounded border-border-dark bg-black/30 text-primary focus:ring-primary" />
                            <label for="feat_{{ $key }}" class="text-sm text-slate-300">Enabled</label>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="flex items-center gap-3">
    <button type="submit"
            class="px-6 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
        Save Changes
    </button>
    <a href="{{ route('super-admin.plans.index') }}"
       class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">Cancel</a>
</div>
</form>
@endsection
