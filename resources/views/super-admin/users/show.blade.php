@extends('layouts.super-admin')
@section('title', $user->name)
@section('page-title', 'User: ' . $user->name)

@section('header-actions')
    <a href="{{ route('super-admin.users.edit', $user) }}"
       class="inline-flex items-center gap-2 px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined !text-sm">edit</span>
        Edit
    </a>
    @unless ($user->hasRole('super_admin'))
        <form method="POST" action="{{ route('super-admin.impersonate', $user) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 text-sm font-medium rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-sm">visibility</span>
                Impersonate
            </button>
        </form>
    @endunless
    <a href="{{ route('super-admin.users.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 bg-white/5 hover:bg-white/10 text-sm rounded-lg transition-colors">
        ← Back
    </a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left column --}}
    <div class="space-y-4">
        {{-- Profile card --}}
        <div class="glass-card rounded-xl p-5">
            <div class="flex items-center gap-4 mb-5">
                <div class="size-16 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-xl">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ $user->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                    <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs bg-primary/10 text-primary">
                        {{ $user->roles->first()?->name ?? 'No role' }}
                    </span>
                </div>
            </div>
            <dl class="space-y-2 text-sm divide-y divide-border-dark">
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">ULID</dt>
                    <dd class="font-mono text-xs text-slate-300">{{ $user->ulid }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Status</dt>
                    <dd>
                        @if ($user->is_active)
                            <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-400">Active</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs bg-red-500/10 text-red-400">Inactive</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Email verified</dt>
                    <dd>{{ $user->email_verified_at ? $user->email_verified_at->format('d M Y') : '—' }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Last login</dt>
                    <dd>{{ $user->last_login_at?->diffForHumans() ?? '—' }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Joined</dt>
                    <dd>{{ $user->created_at->format('d M Y') }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Timezone</dt>
                    <dd>{{ $user->timezone ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Subscription --}}
        <div class="glass-card rounded-xl p-5">
            <h4 class="text-sm font-semibold mb-3">Subscription</h4>
            @if ($user->subscription)
                @php $sub = $user->subscription; @endphp
                <dl class="space-y-2 text-sm divide-y divide-border-dark">
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-400">Plan</dt>
                        <dd><x-plan-badge :plan="$sub->plan" :status="$sub->status" /></dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-400">Gateway</dt>
                        <dd class="capitalize">{{ $sub->gateway }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-400">Period ends</dt>
                        <dd>{{ $sub->current_period_end?->format('d M Y') ?? '—' }}</dd>
                    </div>
                    @if ($sub->trial_ends_at)
                        <div class="flex justify-between py-2">
                            <dt class="text-slate-400">Trial ends</dt>
                            <dd>{{ $sub->trial_ends_at->format('d M Y') }}</dd>
                        </div>
                    @endif
                </dl>
                <div class="mt-3">
                    <a href="{{ route('super-admin.subscriptions.show', $sub) }}"
                       class="text-xs text-primary hover:underline">View subscription →</a>
                </div>
            @else
                <p class="text-sm text-slate-400">No active subscription.</p>
            @endif
        </div>

        {{-- Danger zone --}}
        @unless ($user->hasRole('super_admin'))
        <div class="glass-card rounded-xl p-5 border border-red-500/20">
            <h4 class="text-sm font-semibold text-red-400 mb-3">Danger Zone</h4>
            <div class="space-y-2">
                <form method="POST" action="{{ route('super-admin.users.toggle-active', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="w-full px-3 py-2 text-sm rounded-lg bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 transition-colors">
                        {{ $user->is_active ? 'Deactivate User' : 'Activate User' }}
                    </button>
                </form>
                @if ($user->trashed())
                    <form method="POST" action="{{ route('super-admin.users.restore', $user->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="w-full px-3 py-2 text-sm rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 transition-colors">
                            Restore User
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('super-admin.users.destroy', $user) }}"
                          onsubmit="return confirm('Soft-delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-3 py-2 text-sm rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors">
                            Delete User
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endunless
    </div>

    {{-- Right columns --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- Recent links --}}
        <div class="glass-card rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold">Recent Links</h4>
                <span class="text-xs text-slate-400">{{ $user->links->count() }} shown</span>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-400 uppercase tracking-wider border-b border-border-dark">
                        <th class="text-left py-2 pr-4">Short code</th>
                        <th class="text-left py-2 pr-4">Destination</th>
                        <th class="text-left py-2 pr-4">Clicks</th>
                        <th class="text-left py-2">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark">
                    @forelse ($user->links as $link)
                        <tr>
                            <td class="py-2 pr-4 font-mono text-primary text-xs">{{ $link->short_code }}</td>
                            <td class="py-2 pr-4 text-slate-400 text-xs truncate max-w-48">{{ $link->destination_url }}</td>
                            <td class="py-2 pr-4">{{ number_format($link->clicks_count) }}</td>
                            <td class="py-2 text-slate-400">{{ $link->created_at->format('d M') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-slate-400">No links yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Recent invoices --}}
        <div class="glass-card rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold">Recent Invoices</h4>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-400 uppercase tracking-wider border-b border-border-dark">
                        <th class="text-left py-2 pr-4">Invoice</th>
                        <th class="text-left py-2 pr-4">Amount</th>
                        <th class="text-left py-2 pr-4">Status</th>
                        <th class="text-left py-2">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark">
                    @forelse ($user->invoices as $invoice)
                        <tr>
                            <td class="py-2 pr-4">
                                <a href="{{ route('super-admin.invoices.show', $invoice) }}"
                                   class="font-mono text-xs text-primary hover:underline">{{ $invoice->ulid }}</a>
                            </td>
                            <td class="py-2 pr-4">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                            <td class="py-2 pr-4">
                                <span class="px-2 py-0.5 rounded-full text-xs
                                    {{ $invoice->status === 'paid' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-500/10 text-slate-400' }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="py-2 text-slate-400">{{ $invoice->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-slate-400">No invoices.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
