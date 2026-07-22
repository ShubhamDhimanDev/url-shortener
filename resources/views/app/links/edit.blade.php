@extends('layouts.app')
@section('title', 'Edit Link: ' . ($link->title ?: $link->short_code))
@section('page-title', 'Edit Link')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('app.links.update', $link->ulid) }}" class="space-y-4">
        @csrf
        @method('PUT')

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
                       value="{{ old('destination_url', $link->destination_url) }}"
                       placeholder="https://example.com/your-long-url"
                       class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border @error('destination_url') border-red-400 @else border-slate-200 dark:border-border-dark @enderror rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
                @error('destination_url')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="title" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Title</label>
                <input type="text" name="title" id="title"
                       value="{{ old('title', $link->title) }}"
                       placeholder="My awesome link"
                       class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
            </div>
        </div>

        {{-- Short URL info (read-only) --}}
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined !text-base text-primary">tune</span>
                Short URL
            </h3>
            <div class="flex items-center gap-2">
                <a href="{{ $link->short_url }}" target="_blank" class="text-sm text-primary font-mono">{{ $link->short_url }}</a>
                <span class="text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 px-2 py-0.5 rounded">read-only</span>
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
                               {{ in_array($tag->id, old('tag_ids', $selectedTagIds)) ? 'checked' : '' }}
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

        {{-- Settings --}}
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined !text-base text-primary">settings</span>
                Link Settings
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="expires_at" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Expiry Date</label>
                    <input type="datetime-local" name="expires_at" id="expires_at"
                           value="{{ old('expires_at', $link->expires_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Password</label>
                    <input type="password" name="password" id="password"
                           placeholder="{{ $link->is_password_protected ? 'Change password (leave blank to keep)' : 'Add password protection' }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary" />
                </div>

                <div class="col-span-full flex items-center gap-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $link->is_active) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-10 h-5 bg-slate-200 dark:bg-slate-700 peer-checked:bg-primary rounded-full transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow peer-checked:translate-x-5 transition-transform"></div>
                        </div>
                        <span class="text-sm text-slate-600 dark:text-slate-300">Link is active</span>
                    </label>
                </div>

                <div class="col-span-full flex items-center gap-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="is_bot_protection_enabled" value="1"
                                   {{ old('is_bot_protection_enabled', $link->is_bot_protection_enabled) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-10 h-5 bg-slate-200 dark:bg-slate-700 peer-checked:bg-primary rounded-full transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow peer-checked:translate-x-5 transition-transform"></div>
                        </div>
                        <span class="text-sm text-slate-600 dark:text-slate-300">Enable bot protection</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- UTM --}}
        <details class="glass-card rounded-xl" {{ collect(old())->only(['utm_source','utm_medium','utm_campaign','utm_term','utm_content'])->filter()->isNotEmpty() || $link->utm_source ? 'open' : '' }}>
            <summary class="p-6 cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-2 select-none">
                <span class="material-symbols-outlined !text-base text-primary">campaign</span>
                UTM Campaign Tracking
                <span class="ml-auto material-symbols-outlined !text-base text-slate-400">expand_more</span>
            </summary>
            <div class="px-6 pb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach(['utm_source' => 'Source', 'utm_medium' => 'Medium', 'utm_campaign' => 'Campaign', 'utm_term' => 'Term', 'utm_content' => 'Content'] as $field => $label)
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">{{ $label }}</label>
                    <input type="text" name="{{ $field }}" value="{{ old($field, $link->$field) }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary" />
                </div>
                @endforeach
            </div>
        </details>

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('app.links.show', $link->ulid) }}"
               class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-base">save</span>
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
