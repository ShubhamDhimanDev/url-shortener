@extends('layouts.super-admin')
@section('title', 'Settings')
@section('page-title', 'Platform Settings')

@section('content')
<div class="max-w-4xl">
    <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @forelse ($groups as $groupKey => $groupLabel)
            @php $groupSettings = $settings->get($groupKey, collect()); @endphp

            <div class="glass-card rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-border-dark bg-white/5">
                    <h3 class="text-sm font-semibold">{{ $groupLabel }}</h3>
                </div>

                @if ($groupSettings->isEmpty())
                    <div class="px-5 py-4">
                        <p class="text-sm text-slate-400">No settings in this group yet.
                            <button type="button" class="text-primary hover:underline text-xs ml-1"
                                    onclick="addSetting('{{ $groupKey }}')">Add one?</button>
                        </p>
                    </div>
                @else
                    <div class="divide-y divide-border-dark">
                        @foreach ($groupSettings as $setting)
                            <div class="px-5 py-4 flex items-center gap-4">
                                <div class="w-56 shrink-0">
                                    <p class="text-sm font-medium text-slate-200">{{ $setting->key }}</p>
                                    @if ($setting->cast)
                                        <p class="text-xs text-slate-500 mt-0.5">Type: {{ $setting->cast }}</p>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    @if ($setting->cast === 'boolean')
                                        <div class="flex items-center gap-2">
                                            <input type="hidden"
                                                   name="settings[{{ $groupKey }}][{{ $setting->key }}]"
                                                   value="0">
                                            <input type="checkbox"
                                                   name="settings[{{ $groupKey }}][{{ $setting->key }}]"
                                                   value="1"
                                                   @checked(filter_var($setting->value, FILTER_VALIDATE_BOOLEAN))
                                                   class="rounded border-border-dark bg-black/30 text-primary focus:ring-primary" />
                                            <span class="text-sm text-slate-300">Enabled</span>
                                        </div>
                                    @elseif (strlen($setting->value) > 80 || $setting->cast === 'text')
                                        <textarea name="settings[{{ $groupKey }}][{{ $setting->key }}]"
                                                  rows="3"
                                                  class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary font-mono text-xs">{{ old("settings.{$groupKey}.{$setting->key}", $setting->value) }}</textarea>
                                    @else
                                        <input type="{{ in_array($setting->key, ['password','secret','key']) ? 'password' : 'text' }}"
                                               name="settings[{{ $groupKey }}][{{ $setting->key }}]"
                                               value="{{ old("settings.{$groupKey}.{$setting->key}", $setting->value) }}"
                                               class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="glass-card rounded-xl p-12 text-center text-slate-400">
                No settings configured yet. Settings will appear here once seeded.
            </div>
        @endforelse

        <div class="flex items-center gap-3 sticky bottom-4">
            <button type="submit"
                    class="px-6 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors shadow-lg">
                Save All Settings
            </button>
        </div>
    </form>
</div>
@endsection
