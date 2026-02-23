@extends('layouts.super-admin')
@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('content')
<div class="space-y-4">

    {{-- Filters --}}
    <form method="GET" class="glass-card rounded-xl p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-xs text-slate-400 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="ULID or gateway ID…"
                   class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary" />
        </div>
        <div class="min-w-36">
            <label class="block text-xs text-slate-400 mb-1">Status</label>
            <select name="status" class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
                <option value="">All</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-36">
            <label class="block text-xs text-slate-400 mb-1">Gateway</label>
            <select name="gateway" class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
                <option value="">All</option>
                @foreach ($gateways as $gw)
                    <option value="{{ $gw }}" @selected(request('gateway') === $gw)>{{ ucfirst($gw) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">Filter</button>
            <a href="{{ route('super-admin.invoices.index') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-sm rounded-lg">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="border-b border-border-dark">
                <tr class="text-xs text-slate-400 uppercase tracking-wider">
                    <th class="text-left px-5 py-3">Invoice</th>
                    <th class="text-left px-5 py-3">Subscriber</th>
                    <th class="text-left px-5 py-3">Plan</th>
                    <th class="text-left px-5 py-3">Amount</th>
                    <th class="text-left px-5 py-3">Status</th>
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-dark">
                @forelse ($invoices as $invoice)
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-5 py-3">
                            <a href="{{ route('super-admin.invoices.show', $invoice) }}"
                               class="font-mono text-xs text-primary hover:underline">{{ $invoice->ulid }}</a>
                        </td>
                        <td class="px-5 py-3 text-slate-400">{{ $invoice->subscribable?->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <x-plan-badge :plan="$invoice->plan" />
                        </td>
                        <td class="px-5 py-3 font-medium">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                        <td class="px-5 py-3">
                            @php
                                $statusColors = [
                                    'paid'          => 'bg-emerald-500/10 text-emerald-400',
                                    'open'          => 'bg-blue-500/10 text-blue-400',
                                    'draft'         => 'bg-slate-500/10 text-slate-400',
                                    'void'          => 'bg-red-500/10 text-red-400',
                                    'uncollectible' => 'bg-amber-500/10 text-amber-400',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $statusColors[$invoice->status] ?? 'bg-slate-500/10 text-slate-400' }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-400">{{ $invoice->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('super-admin.invoices.download', $invoice) }}"
                                   class="text-slate-400 hover:text-primary transition-colors" title="Download PDF">
                                    <span class="material-symbols-outlined !text-base">download</span>
                                </a>
                                @if (! in_array($invoice->status, ['void', 'paid']))
                                    <form method="POST" action="{{ route('super-admin.invoices.void', $invoice) }}"
                                          onsubmit="return confirm('Void this invoice?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-slate-400 hover:text-red-400 transition-colors" title="Void">
                                            <span class="material-symbols-outlined !text-base">block</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">No invoices found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($invoices->hasPages())
            <div class="px-5 py-4 border-t border-border-dark">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
@endsection
