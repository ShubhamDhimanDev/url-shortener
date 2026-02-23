@extends('layouts.app')
@section('title', 'Billing')
@section('page-title', 'Billing')

@section('content')
<div class="max-w-4xl space-y-6">

    {{-- ── Current plan ─────────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Current Plan</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white">
                    {{ $activePlan?->name ?? 'Free' }}
                </h3>
                @if($subscription)
                    <p class="text-sm mt-1
                        @if($subscription->status === 'active') text-green-500
                        @elseif($subscription->status === 'trialing') text-yellow-500
                        @elseif($subscription->status === 'cancelled') text-red-500
                        @else text-slate-400 @endif">
                        @if($subscription->status === 'trialing')
                            Trial ends {{ $subscription->trial_ends_at?->format('M j, Y') }}
                        @elseif($subscription->status === 'cancelled')
                            Cancelled · Access until {{ $subscription->ends_at?->format('M j, Y') }}
                        @else
                            Active · Renews {{ $subscription->renews_at?->format('M j, Y') }}
                        @endif
                    </p>
                @else
                    <p class="text-xs text-slate-400 mt-1">Free plan — no payment required</p>
                @endif
            </div>

            <div class="flex gap-2">
                <a href="{{ route('app.billing.plans') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                    <span class="material-symbols-outlined !text-sm">upgrade</span>
                    Upgrade Plan
                </a>
                @if($subscription && !in_array($subscription->status, ['cancelled', 'pending_cancellation']))
                    <form method="POST" action="{{ route('app.billing.cancel') }}"
                          onsubmit="return confirm('Cancel your subscription? You\'ll keep access until the billing period ends.')">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 border border-red-400 text-red-500 text-sm font-medium rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                            Cancel Plan
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if($activePlan)
            <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($activePlan->features as $feature)
                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span class="material-symbols-outlined !text-sm text-green-400">check_circle</span>
                        {{ $feature->description ?? $feature->feature_key }}
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Payment methods ──────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">
            <span class="material-symbols-outlined !text-base align-middle mr-1 text-primary">credit_card</span>
            Payment Methods
        </h3>

        @if($paymentMethods->isNotEmpty())
            <div class="space-y-2">
                @foreach($paymentMethods as $pm)
                    <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                        <span class="material-symbols-outlined !text-lg text-primary">
                            {{ $pm->type === 'card' ? 'credit_card' : 'account_balance' }}
                        </span>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100 capitalize">{{ $pm->type }}</p>
                            <p class="text-xs text-slate-400">{{ $pm->last_four ? '•••• ' . $pm->last_four : $pm->provider_payment_id }}</p>
                        </div>
                        @if($pm->is_default)
                            <span class="text-xs px-2 py-0.5 bg-primary/10 text-primary rounded-full font-medium">Default</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-400">No payment methods saved.</p>
        @endif
    </div>

    {{-- ── Invoices ──────────────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">
            <span class="material-symbols-outlined !text-base align-middle mr-1 text-primary">receipt_long</span>
            Billing History
        </h3>

        @if($invoices->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-slate-400 uppercase tracking-wide border-b border-slate-200 dark:border-border-dark">
                            <th class="py-2 text-left font-medium">Invoice</th>
                            <th class="py-2 text-left font-medium">Date</th>
                            <th class="py-2 text-left font-medium">Amount</th>
                            <th class="py-2 text-left font-medium">Status</th>
                            <th class="py-2 text-right font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                        @foreach($invoices as $invoice)
                            <tr>
                                <td class="py-3 font-mono text-xs text-slate-500">{{ $invoice->invoice_number ?? '#' . $invoice->id }}</td>
                                <td class="py-3 text-slate-600 dark:text-slate-300">{{ $invoice->created_at->format('M j, Y') }}</td>
                                <td class="py-3 font-semibold text-slate-800 dark:text-slate-100">
                                    {{ strtoupper($invoice->currency ?? 'INR') }}
                                    {{ number_format($invoice->amount / 100, 2) }}
                                </td>
                                <td class="py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize
                                        @if($invoice->status === 'paid') bg-green-100 dark:bg-green-900/30 text-green-600
                                        @elseif($invoice->status === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600
                                        @else bg-red-100 dark:bg-red-900/30 text-red-600 @endif">
                                        {{ $invoice->status }}
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('app.billing.invoices.download', $invoice->id) }}"
                                       class="inline-flex items-center gap-1 text-xs text-primary hover:underline">
                                        <span class="material-symbols-outlined !text-sm">download</span>
                                        PDF
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $invoices->links() }}</div>
        @else
            <p class="text-sm text-slate-400">No invoices yet.</p>
        @endif
    </div>

</div>
@endsection
