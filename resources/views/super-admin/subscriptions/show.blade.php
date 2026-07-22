@extends('layouts.super-admin')
@section('title', 'Subscription #' . $subscription->ulid)
@section('page-title', 'Subscription Detail')

@section('header-actions')
    <a href="{{ route('super-admin.subscriptions.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 bg-white/5 hover:bg-white/10 text-sm rounded-lg transition-colors">← Back</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Info --}}
    <div class="space-y-4">
        <div class="glass-card rounded-xl p-5">
            <h4 class="text-sm font-semibold mb-3">Subscription Info</h4>
            <dl class="space-y-2 text-sm divide-y divide-border-dark">
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">ULID</dt>
                    <dd class="font-mono text-xs">{{ $subscription->ulid }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Subscriber</dt>
                    <dd>
                        @if ($subscription->subscribable)
                            <a href="{{ route('super-admin.users.show', $subscription->subscribable) }}"
                               class="text-primary hover:underline">{{ $subscription->subscribable->name }}</a>
                        @else —
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Type</dt>
                    <dd>{{ class_basename($subscription->subscribable_type) }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Plan</dt>
                    <dd><x-plan-badge :plan="$subscription->plan" :status="$subscription->status" /></dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Gateway</dt>
                    <dd class="capitalize">{{ $subscription->gateway }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Gateway Sub ID</dt>
                    <dd class="font-mono text-xs text-slate-300">{{ $subscription->gateway_subscription_id ?? '—' }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Gateway Customer ID</dt>
                    <dd class="font-mono text-xs text-slate-300">{{ $subscription->gateway_customer_id ?? '—' }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Trial ends</dt>
                    <dd>{{ $subscription->trial_ends_at?->format('d M Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Period start</dt>
                    <dd>{{ $subscription->current_period_start?->format('d M Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Period end</dt>
                    <dd>{{ $subscription->current_period_end?->format('d M Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Cancelled at</dt>
                    <dd>{{ $subscription->cancelled_at?->format('d M Y') ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Actions --}}
        <div class="glass-card rounded-xl p-5">
            <h4 class="text-sm font-semibold mb-3">Actions</h4>
            @if (! in_array($subscription->status, ['cancelled', 'expired']))
                <form method="POST" action="{{ route('super-admin.subscriptions.cancel', $subscription) }}"
                      onsubmit="return confirm('Cancel this subscription?')" class="mb-2">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="w-full px-3 py-2 text-sm rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors">
                        Cancel Subscription
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Manual override + invoices --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- Override form --}}
        <div class="glass-card rounded-xl p-5">
            <h4 class="text-sm font-semibold mb-4">Manual Override</h4>
            <form method="POST" action="{{ route('super-admin.subscriptions.update', $subscription) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Plan</label>
                        <select name="plan_id" class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
                            @foreach (\App\Models\Plan::orderBy('name')->get() as $plan)
                                <option value="{{ $plan->id }}" @selected($plan->id === $subscription->plan_id)>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Status</label>
                        <select name="status" class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
                            @foreach (['trialing','active','past_due','cancelled','expired'] as $s)
                                <option value="{{ $s }}" @selected($s === $subscription->status)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Period Start</label>
                        <input type="datetime-local" name="current_period_start"
                               value="{{ $subscription->current_period_start?->format('Y-m-d\TH:i') }}"
                               class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Period End</label>
                        <input type="datetime-local" name="current_period_end"
                               value="{{ $subscription->current_period_end?->format('Y-m-d\TH:i') }}"
                               class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Ends At</label>
                        <input type="datetime-local" name="ends_at"
                               value="{{ $subscription->ends_at?->format('Y-m-d\TH:i') }}"
                               class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
                    </div>
                </div>
                <button type="submit"
                        class="px-5 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
                    Apply Override
                </button>
            </form>
        </div>

        {{-- Related invoices --}}
        <div class="glass-card rounded-xl p-5">
            <h4 class="text-sm font-semibold mb-3">Related Invoices</h4>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-400 uppercase tracking-wider border-b border-border-dark">
                        <th class="text-left py-2 pr-4">Invoice</th>
                        <th class="text-left py-2 pr-4">Total</th>
                        <th class="text-left py-2 pr-4">Status</th>
                        <th class="text-left py-2">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark">
                    @forelse ($subscription->invoices as $invoice)
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
