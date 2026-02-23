@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}"
   class="{{ $active
       ? 'sidebar-item-active flex items-center gap-3 px-3 py-2 text-primary font-medium rounded-lg'
       : 'flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-primary/10 hover:text-primary transition-all rounded-lg' }}">
    <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
    <span class="text-sm">{{ $slot }}</span>
</a>
