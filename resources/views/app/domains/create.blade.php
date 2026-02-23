@extends('layouts.app')
@section('title', 'Add Custom Domain')
@section('page-title', 'Add Custom Domain')

@section('content')
<div class="max-w-2xl space-y-6">

    {{-- Instructions --}}
    <div class="glass-card rounded-xl p-6 flex gap-4">
        <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-primary">info</span>
        </div>
        <div>
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">How it works</h3>
            <ol class="text-sm text-slate-500 dark:text-slate-400 space-y-1 list-decimal list-inside">
                <li>Add your domain or subdomain below.</li>
                <li>Add a DNS TXT record with the verification token we provide.</li>
                <li>Click "Verify" to confirm ownership.</li>
                <li>Point a CNAME record to <code class="font-mono text-primary">{{ config('app.url') }}</code>.</li>
            </ol>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('app.domains.store') }}" class="glass-card rounded-xl p-6 space-y-5">
        @csrf

        <div>
            <label for="domain" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">
                Domain Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="domain" id="domain"
                   value="{{ old('domain') }}"
                   placeholder="links.yourdomain.com"
                   class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border @error('domain') border-red-400 @else border-slate-200 dark:border-border-dark @enderror rounded-lg focus:outline-none focus:ring-1 focus:ring-primary font-mono transition" />
            @error('domain')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-[10px] text-slate-400 mt-1">Enter a subdomain (links.yourdomain.com) or a root domain (yourdomain.com).</p>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Domain Type</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="type" value="custom_domain"
                           {{ old('type', 'custom_domain') === 'custom_domain' ? 'checked' : '' }}
                           class="text-primary focus:ring-primary" />
                    <span class="text-sm text-slate-600 dark:text-slate-300">Custom Domain</span>
                </label>
                @php
                    $canSubdomain = \App\Services\FeatureService::for(auth()->user())->can('custom_subdomain');
                @endphp
                <label class="flex items-center gap-2 cursor-pointer {{ $canSubdomain ? '' : 'opacity-40' }}">
                    <input type="radio" name="type" value="custom_subdomain"
                           {{ old('type') === 'custom_subdomain' ? 'checked' : '' }}
                           {{ $canSubdomain ? '' : 'disabled' }}
                           class="text-primary focus:ring-primary" />
                    <span class="text-sm text-slate-600 dark:text-slate-300">
                        Platform Subdomain
                        @if(! $canSubdomain)
                            <span class="text-[10px] text-slate-400">(upgrade required)</span>
                        @endif
                    </span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('app.domains.index') }}"
               class="text-sm text-slate-500 hover:text-slate-700 transition">Cancel</a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-base">add</span>
                Add Domain
            </button>
        </div>
    </form>

</div>
@endsection
