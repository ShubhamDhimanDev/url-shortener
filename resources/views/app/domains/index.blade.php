@extends('layouts.app')
@section('title', 'Custom Domains')
@section('page-title', 'Custom Domains')

@section('content')
<div class="space-y-4">

    {{-- ── Add domain CTA ──────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Connect your own domain to use it for short links.
        </p>
        <a href="{{ route('app.domains.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
            <span class="material-symbols-outlined !text-base">add</span>
            Add Domain
        </a>
    </div>

    {{-- ── Domain list ─────────────────────────────────────────────────── --}}
    @forelse ($domains as $domain)
        <div class="glass-card rounded-xl p-5 flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-100 font-mono">
                        {{ $domain->domain }}
                    </span>

                    {{-- Verification status --}}
                    @if($domain->is_verified)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-green-100 dark:bg-green-900/30 text-green-600">
                            <span class="material-symbols-outlined !text-xs">verified</span> Verified
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-600">
                            <span class="material-symbols-outlined !text-xs">pending</span> Pending verification
                        </span>
                    @endif

                    {{-- SSL status --}}
                    @if($domain->ssl_status === 'active')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600">
                            <span class="material-symbols-outlined !text-xs">lock</span> SSL Active
                        </span>
                    @elseif($domain->ssl_status === 'failed')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-red-100 dark:bg-red-900/30 text-red-500">
                            <span class="material-symbols-outlined !text-xs">no_encryption</span> SSL Failed
                        </span>
                    @endif

                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">
                        {{ str_replace('_', ' ', $domain->type) }}
                    </span>
                </div>

                <p class="text-xs text-slate-400 mt-1">
                    {{ $domain->links_count }} link{{ $domain->links_count !== 1 ? 's' : '' }} using this domain
                    · Added {{ $domain->created_at->diffForHumans() }}
                </p>

                {{-- DNS instructions (if not verified) --}}
                @if(! $domain->is_verified)
                    <div class="mt-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/50">
                        <p class="text-xs font-medium text-amber-700 dark:text-amber-400 mb-1">Add this DNS TXT record to verify ownership:</p>
                        <code class="text-xs font-mono text-amber-800 dark:text-amber-300 break-all">
                            {{ $domain->verification_token }}
                        </code>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if(! $domain->is_verified)
                    <form method="POST" action="{{ route('app.domains.verify', $domain) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-amber-300 dark:border-amber-700 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition">
                            <span class="material-symbols-outlined !text-sm">refresh</span> Verify
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('app.domains.destroy', $domain) }}"
                      onsubmit="return confirm('Remove {{ $domain->domain }}? Links using this domain will fall back to the default domain.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-slate-200 dark:border-border-dark text-slate-500 hover:border-red-400 hover:text-red-500 rounded-lg transition">
                        <span class="material-symbols-outlined !text-sm">delete</span> Remove
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="glass-card rounded-xl p-12 text-center">
            <span class="material-symbols-outlined !text-5xl text-slate-300 dark:text-slate-700">language</span>
            <p class="mt-3 text-slate-500 dark:text-slate-400">No custom domains yet.</p>
            <a href="{{ route('app.domains.create') }}"
               class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-base">add</span>
                Add Your First Domain
            </a>
        </div>
    @endforelse

    @if($domains->hasPages())
        <div class="flex justify-center">{{ $domains->links() }}</div>
    @endif

</div>
@endsection
