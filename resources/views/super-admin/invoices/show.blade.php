@extends('layouts.super-admin')
@section('title', 'Invoice ' . $invoice->ulid)
@section('page-title', 'Invoice Detail')

@section('header-actions')
    <a href="{{ route('super-admin.invoices.download', $invoice) }}"
       class="inline-flex items-center gap-2 px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined !text-sm">download</span>
        Download PDF
    </a>
    @if (! in_array($invoice->status, ['void', 'paid']))
        <form method="POST" action="{{ route('super-admin.invoices.void', $invoice) }}"
              onsubmit="return confirm('Void this invoice?')">
            @csrf
            @method('PATCH')
            <button type="submit"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 text-sm font-medium rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-sm">block</span>
                Void Invoice
            </button>
        </form>
    @endif
    <a href="{{ route('super-admin.invoices.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 bg-white/5 hover:bg-white/10 text-sm rounded-lg transition-colors">← Back</a>
@endsection

@section('content')
<div class="max-w-3xl">
    <div class="glass-card rounded-xl p-6">
        {{-- Header --}}
        <div class="flex items-start justify-between mb-6 pb-6 border-b border-border-dark">
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Invoice</p>
                <p class="text-2xl font-bold font-mono">{{ $invoice->ulid }}</p>
                @php
                    $statusColors = [
                        'paid'          => 'bg-emerald-500/10 text-emerald-400',
                        'open'          => 'bg-blue-500/10 text-blue-400',
                        'draft'         => 'bg-slate-500/10 text-slate-400',
                        'void'          => 'bg-red-500/10 text-red-400',
                        'uncollectible' => 'bg-amber-500/10 text-amber-400',
                    ];
                @endphp
                <span class="mt-2 inline-block px-3 py-1 rounded-full text-sm {{ $statusColors[$invoice->status] ?? '' }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-400">Total Amount</p>
                <p class="text-3xl font-bold text-white">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</p>
            </div>
        </div>

        {{-- Details grid --}}
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-3">Bill To</p>
                <p class="font-medium">{{ $invoice->subscribable?->name ?? '—' }}</p>
                <p class="text-sm text-slate-400">{{ $invoice->subscribable?->email ?? '' }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ class_basename($invoice->subscribable_type) }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-3">Details</p>
                <dl class="space-y-1 text-sm">
                    <div class="flex gap-2">
                        <dt class="text-slate-400 w-28">Plan</dt>
                        <dd><x-plan-badge :plan="$invoice->plan" /></dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="text-slate-400 w-28">Gateway</dt>
                        <dd class="capitalize">{{ $invoice->gateway }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="text-slate-400 w-28">Gateway Invoice</dt>
                        <dd class="font-mono text-xs">{{ $invoice->gateway_invoice_id ?? '—' }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="text-slate-400 w-28">Gateway Payment</dt>
                        <dd class="font-mono text-xs">{{ $invoice->gateway_payment_id ?? '—' }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="text-slate-400 w-28">Due Date</dt>
                        <dd>{{ $invoice->due_at?->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="text-slate-400 w-28">Paid At</dt>
                        <dd>{{ $invoice->paid_at?->format('d M Y H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Line items --}}
        <div class="border border-border-dark rounded-lg overflow-hidden mb-6">
            <table class="w-full text-sm">
                <thead class="bg-white/5">
                    <tr class="text-xs text-slate-400 uppercase tracking-wider">
                        <th class="text-left px-4 py-3">Description</th>
                        <th class="text-right px-4 py-3">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t border-border-dark">
                        <td class="px-4 py-3">{{ $invoice->description ?: ($invoice->plan?->name . ' Subscription') }}</td>
                        <td class="px-4 py-3 text-right">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot class="bg-white/5 border-t border-border-dark">
                    <tr>
                        <td class="px-4 py-2 text-right text-slate-400 text-xs">Subtotal</td>
                        <td class="px-4 py-2 text-right">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 text-right text-slate-400 text-xs">Tax</td>
                        <td class="px-4 py-2 text-right">{{ $invoice->currency }} {{ number_format($invoice->tax ?? 0, 2) }}</td>
                    </tr>
                    <tr class="font-semibold">
                        <td class="px-4 py-3 text-right">Total</td>
                        <td class="px-4 py-3 text-right text-primary">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Metadata --}}
        @if ($invoice->metadata)
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-2">Metadata</p>
                <pre class="bg-black/40 rounded-lg p-4 text-xs text-slate-300 overflow-x-auto">{{ json_encode($invoice->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>
</div>
@endsection
