@props(['link'])

<div class="glass-card rounded-xl p-4 flex gap-4 items-start group hover:border-primary/40 transition-all duration-200 neon-glow">
    {{-- Favicon --}}
    <div class="size-10 rounded-lg bg-slate-200 dark:bg-slate-800 flex items-center justify-center shrink-0 overflow-hidden mt-0.5">
        <img src="https://www.google.com/s2/favicons?domain={{ parse_url($link->destination_url, PHP_URL_HOST) }}&sz=64"
             class="size-8 object-contain"
             loading="lazy"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
             alt="">
        <span class="material-symbols-outlined text-slate-400 !text-xl hidden">link</span>
    </div>

    {{-- Content --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('app.links.show', $link->ulid) }}"
               class="text-sm font-semibold text-slate-800 dark:text-slate-100 hover:text-primary transition truncate max-w-xs">
                {{ $link->title ?: $link->short_code }}
            </a>

            @if(! $link->is_active)
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                    <span class="size-1.5 rounded-full bg-slate-400"></span>Inactive
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                    <span class="size-1.5 rounded-full bg-green-500"></span>Active
                </span>
            @endif

            @if($link->is_password_protected)
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <span class="material-symbols-outlined !text-[10px]">lock</span> Protected
                </span>
            @endif

            @if($link->expires_at && $link->expires_at->isPast())
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 dark:bg-red-900/30 text-red-500">
                    Expired
                </span>
            @endif
        </div>

        {{-- Short URL --}}
        <div class="flex items-center gap-1.5 mt-0.5">
            <a href="{{ $link->short_url }}" target="_blank"
               class="text-xs text-primary hover:underline font-medium">
                {{ $link->short_url }}
            </a>
            <button
                onclick="navigator.clipboard.writeText('{{ $link->short_url }}').then(() => this.innerHTML = '<span class=\'material-symbols-outlined !text-sm\'>check</span>')"
                class="text-slate-400 hover:text-primary transition-colors"
                title="Copy URL">
                <span class="material-symbols-outlined !text-sm">content_copy</span>
            </button>
        </div>

        {{-- Destination URL --}}
        <p class="text-[11px] text-slate-400 truncate mt-0.5" title="{{ $link->destination_url }}">
            {{ $link->destination_url }}
        </p>

        {{-- Tags --}}
        @if($link->tags->isNotEmpty())
            <div class="flex flex-wrap gap-1 mt-1.5">
                @foreach($link->tags as $tag)
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium"
                          style="background-color: {{ $tag->color }}22; color: {{ $tag->color }};">
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Stats + Actions --}}
    <div class="flex flex-col items-end gap-2 shrink-0">
        <div class="flex items-center gap-1 text-xs font-semibold text-slate-700 dark:text-slate-300">
            <span class="material-symbols-outlined !text-sm text-primary">ads_click</span>
            {{ number_format($link->clicks_count) }}
        </div>
        <p class="text-[10px] text-slate-400">{{ $link->created_at->diffForHumans() }}</p>

        {{-- Actions --}}
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <a href="{{ route('app.analytics.show', $link->ulid) }}"
               class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 hover:text-primary transition-colors"
               title="Analytics">
                <span class="material-symbols-outlined !text-base">bar_chart</span>
            </a>
            <a href="{{ route('app.links.edit', $link->ulid) }}"
               class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 hover:text-blue-400 transition-colors"
               title="Edit">
                <span class="material-symbols-outlined !text-base">edit</span>
            </a>
            <form method="POST" action="{{ route('app.links.toggle', $link->ulid) }}" class="inline">
                @csrf @method('PATCH')
                <button type="submit"
                        class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 hover:text-amber-400 transition-colors"
                        title="{{ $link->is_active ? 'Deactivate' : 'Activate' }}">
                    <span class="material-symbols-outlined !text-base">{{ $link->is_active ? 'toggle_on' : 'toggle_off' }}</span>
                </button>
            </form>
            <form method="POST" action="{{ route('app.links.destroy', $link->ulid) }}" class="inline"
                  onsubmit="return confirm('Delete this link? This action cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 hover:text-red-400 transition-colors"
                        title="Delete">
                    <span class="material-symbols-outlined !text-base">delete</span>
                </button>
            </form>
        </div>
    </div>
</div>
