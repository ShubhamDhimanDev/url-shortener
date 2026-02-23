@extends('layouts.app')
@section('title', 'Create Link')
@section('page-title', 'Create New Link')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('app.links.store') }}" class="space-y-4">
        @csrf

        {{-- Destination URL --}}
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined !text-base text-primary">link</span>
                Destination URL
            </h3>

            <div>
                <label for="destination_url" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">
                    Long URL <span class="text-red-500">*</span>
                </label>
                <input type="url" name="destination_url" id="destination_url"
                       value="{{ old('destination_url') }}"
                       placeholder="https://example.com/your-long-url"
                       class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border @error('destination_url') border-red-400 @else border-slate-200 dark:border-border-dark @enderror rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
                @error('destination_url')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="title" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Title (optional)</label>
                <input type="text" name="title" id="title"
                       value="{{ old('title') }}"
                       placeholder="My awesome link"
                       class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
            </div>
        </div>

        {{-- Short URL customisation --}}
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined !text-base text-primary">tune</span>
                Short URL
            </h3>

            <div class="flex gap-3 items-end">
                {{-- Domain --}}
                @if($domains->isNotEmpty())
                <div class="shrink-0">
                    <label for="domain_id" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Domain</label>
                    <select name="domain_id" id="domain_id"
                            class="text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Default domain</option>
                        @foreach($domains as $domain)
                            <option value="{{ $domain->id }}" {{ old('domain_id') == $domain->id ? 'selected' : '' }}>
                                {{ $domain->domain }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Custom slug --}}
                <div class="flex-1">
                    <label for="short_code" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Custom Slug (optional)</label>
                    <div class="flex items-center">
                        <span class="text-sm text-slate-400 bg-slate-100 dark:bg-slate-800 border border-r-0 border-slate-200 dark:border-border-dark px-3 py-2.5 rounded-l-lg">/</span>
                        <input type="text" name="short_code" id="short_code"
                               value="{{ old('short_code') }}"
                               placeholder="my-slug"
                               class="flex-1 px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border @error('short_code') border-red-400 @else border-slate-200 dark:border-border-dark @enderror rounded-r-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
                    </div>
                    @error('short_code')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[10px] text-slate-400 mt-1">Leave blank to auto-generate. Only letters, numbers, hyphens and underscores.</p>
                </div>
            </div>
        </div>

        {{-- Tags --}}
        @if($tags->isNotEmpty())
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined !text-base text-primary">label</span>
                Tags
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($tags as $tag)
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                               {{ in_array($tag->id, old('tag_ids', [])) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-primary focus:ring-primary">
                        <span class="text-sm px-2 py-0.5 rounded-full"
                              style="background-color: {{ $tag->color }}22; color: {{ $tag->color }};">
                            {{ $tag->name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Link settings --}}
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined !text-base text-primary">settings</span>
                Link Settings
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Expiry --}}
                <div>
                    <label for="expires_at" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">
                        Expiry Date
                        <x-feature-gate feature="link_expiry" label="Link Expiry">
                            <span class="text-[10px] text-slate-400">(optional)</span>
                        </x-feature-gate>
                    </label>
                    <input type="datetime-local" name="expires_at" id="expires_at"
                           value="{{ old('expires_at') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary" />
                    @error('expires_at')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">
                        Password Protection
                    </label>
                    @php
                        $pwProtected = \App\Services\FeatureService::for(auth()->user())->can('password_protected_links');
                    @endphp
                    @if($pwProtected)
                    <input type="password" name="password" id="password"
                           placeholder="Leave blank for no password"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary" />
                    @else
                        <div class="px-4 py-2.5 rounded-lg border border-dashed border-slate-200 dark:border-border-dark text-xs text-slate-400 flex items-center gap-2">
                            <span class="material-symbols-outlined !text-sm">lock</span>
                            Upgrade to unlock password protection
                        </div>
                    @endif
                </div>

                {{-- Bot protection --}}
                <div class="col-span-full flex items-center gap-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="is_bot_protection_enabled" id="bot_protection" value="1"
                                   {{ old('is_bot_protection_enabled') ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-10 h-5 bg-slate-200 dark:bg-slate-700 peer-checked:bg-primary rounded-full transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow peer-checked:translate-x-5 transition-transform"></div>
                        </div>
                        <span class="text-sm text-slate-600 dark:text-slate-300">Enable bot protection</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- UTM Parameters --}}
        <details class="glass-card rounded-xl">
            <summary class="p-6 cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-2 select-none">
                <span class="material-symbols-outlined !text-base text-primary">campaign</span>
                UTM Campaign Tracking
                <span class="ml-auto material-symbols-outlined !text-base text-slate-400">expand_more</span>
            </summary>
            <div class="px-6 pb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach(['utm_source' => 'Source (e.g. google)', 'utm_medium' => 'Medium (e.g. email)', 'utm_campaign' => 'Campaign name', 'utm_term' => 'Term (keywords)', 'utm_content' => 'Content (A/B test)'] as $field => $placeholder)
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">{{ $field }}</label>
                        <input type="text" name="{{ $field }}" value="{{ old($field) }}"
                               placeholder="{{ $placeholder }}"
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>
                @endforeach
            </div>
        </details>

        {{-- Meta tracking --}}
        @php
            $canMeta   = \App\Services\FeatureService::for(auth()->user())->can('meta_tracking');
            $canGoogle = \App\Services\FeatureService::for(auth()->user())->can('google_tracking');
        @endphp

        @if($canMeta || $canGoogle)
        <details class="glass-card rounded-xl">
            <summary class="p-6 cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-2 select-none">
                <span class="material-symbols-outlined !text-base text-primary">analytics</span>
                Pixel & Tag Tracking
                <span class="ml-auto material-symbols-outlined !text-base text-slate-400">expand_more</span>
            </summary>
            <div class="px-6 pb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($canMeta)
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Meta Pixel ID</label>
                    <input type="text" name="meta_pixel_id" value="{{ old('meta_pixel_id') }}"
                           placeholder="123456789012345"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary" />
                </div>
                @endif
                @if($canGoogle)
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Google Tag ID</label>
                    <input type="text" name="google_tag_id" value="{{ old('google_tag_id') }}"
                           placeholder="G-XXXXXXXXXX"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary" />
                </div>
                @endif
            </div>
        </details>
        @endif

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('app.links.index') }}"
               class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-base">add_link</span>
                Create Short Link
            </button>
        </div>
    </form>
</div>
@endsection
