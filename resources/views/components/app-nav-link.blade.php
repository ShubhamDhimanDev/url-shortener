@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}"
   @class([
       'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
       'sidebar-item-active text-primary'  =>  $active,
       'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-slate-200' => ! $active,
   ])>
    <span class="material-symbols-outlined !text-[18px] shrink-0">{{ $icon }}</span>
    <span>{{ $slot }}</span>
</a>
