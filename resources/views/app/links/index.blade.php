@extends('layouts.app')
@section('title', 'My Links')
@section('page-title', 'My Links')

@section('content')
<div class="space-y-4">

    {{-- ── Filters & search ────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('app.links.index') }}" class="glass-card rounded-xl p-4 flex flex-wrap gap-3 items-end">
        {{-- Search --}}
        <div class="flex-1 min-w-48">
            <label class="block text-xs text-slate-400 mb-1">Search</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined !text-sm text-slate-400">search</span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search title, URL or slug…"
                       class="w-full pl-8 pr-3 py-2 text-sm bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary" />
            </div>
        </div>

        {{-- Status filter --}}
        <div>
            <label class="block text-xs text-slate-400 mb-1">Status</label>
            <select name="status"
                    class="text-sm bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary">
                <option value="">All</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        {{-- Tag filter --}}
        @if($tags->isNotEmpty())
        <div>
            <label class="block text-xs text-slate-400 mb-1">Tag</label>
            <select name="tag_id"
                    class="text-sm bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary">
                <option value="">All Tags</option>
                @foreach($tags as $tag)
                    <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                        {{ $tag->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Domain filter --}}
        @if($domains->isNotEmpty())
        <div>
            <label class="block text-xs text-slate-400 mb-1">Domain</label>
            <select name="domain_id"
                    class="text-sm bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary">
                <option value="">All Domains</option>
                @foreach($domains as $domain)
                    <option value="{{ $domain->id }}" {{ request('domain_id') == $domain->id ? 'selected' : '' }}>
                        {{ $domain->domain }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Sort --}}
        <div>
            <label class="block text-xs text-slate-400 mb-1">Sort</label>
            <select name="sort"
                    class="text-sm bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary">
                <option value="latest"  {{ request('sort', 'latest') === 'latest'  ? 'selected' : '' }}>Newest first</option>
                <option value="oldest"  {{ request('sort') === 'oldest'  ? 'selected' : '' }}>Oldest first</option>
                <option value="clicks"  {{ request('sort') === 'clicks'  ? 'selected' : '' }}>Most clicks</option>
                <option value="title"   {{ request('sort') === 'title'   ? 'selected' : '' }}>Title A-Z</option>
            </select>
        </div>

        <button type="submit"
                class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
            Filter
        </button>

        @if(request()->hasAny(['search','status','tag_id','domain_id','sort']))
            <a href="{{ route('app.links.index') }}"
               class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-border-dark transition-colors">
                Clear
            </a>
        @endif
    </form>

    {{-- ── Link list ────────────────────────────────────────────────────── --}}
    @forelse ($links as $link)
        <x-link-card :link="$link" />
    @empty
        <div class="glass-card rounded-xl p-12 text-center">
            <span class="material-symbols-outlined !text-5xl text-slate-300 dark:text-slate-700">link_off</span>
            <p class="mt-3 text-slate-500 dark:text-slate-400">
                @if(request()->hasAny(['search','status','tag_id','domain_id']))
                    No links match your filters.
                @else
                    You haven't created any links yet.
                @endif
            </p>
            <a href="{{ route('app.links.create') }}"
               class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-base">add</span>
                Create Your First Link
            </a>
        </div>
    @endforelse

    {{-- ── Pagination ──────────────────────────────────────────────────── --}}
    @if($links->hasPages())
        <div class="flex justify-center">
            {{ $links->links() }}
        </div>
    @endif

</div>
@endsection
