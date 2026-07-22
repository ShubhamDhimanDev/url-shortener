<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Invoice {{ $invoice->ulid }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 13px; color: #1a1a1a; padding: 40px; }
    .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
    .company-name { font-size: 22px; font-weight: bold; color: #9a28eb; }
    .invoice-title { font-size: 28px; font-weight: bold; color: #1a1a1a; }
    .meta { margin-bottom: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    .meta-block p { margin-bottom: 4px; }
    .meta-block .label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.05em; }
    .meta-block .value { font-weight: 600; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th { background: #f3f0f8; color: #6b21a8; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;
         padding: 10px 14px; text-align: left; border-bottom: 2px solid #e4d9f5; }
    td { padding: 10px 14px; border-bottom: 1px solid #f0ebf8; }
    .text-right { text-align: right; }
    .totals { margin-left: auto; width: 260px; }
    .totals tr td:first-child { color: #666; }
    .totals tr.grand-total td { font-weight: bold; font-size: 15px; border-top: 2px solid #9a28eb; padding-top: 10px; color: #9a28eb; }
    .status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .status-paid { background: #d1fae5; color: #065f46; }
    .status-open { background: #dbeafe; color: #1e40af; }
    .status-void { background: #fee2e2; color: #991b1b; }
    .footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #888; font-size: 11px; text-align: center; }
</style>
</head>
<body>
<div class="header">
    <div>
        <p class="company-name">{{ config('app.name') }}</p>
        <p style="color:#888; margin-top:4px;">Invoice</p>
    </div>
    <div style="text-align:right;">
        <p class="invoice-title">{{ strtoupper($invoice->ulid) }}</p>
        <span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
    </div>
</div>

<div class="meta">
    <div class="meta-block">
        <p class="label">Bill To</p>
        <p class="value">{{ $invoice->subscribable?->name ?? '—' }}</p>
        <p>{{ $invoice->subscribable?->email ?? '' }}</p>
    </div>
    <div class="meta-block">
        <p class="label">Invoice Date</p>
        <p class="value">{{ $invoice->created_at->format('d F Y') }}</p>
        @if ($invoice->due_at)
            <p class="label" style="margin-top:8px;">Due Date</p>
            <p class="value">{{ $invoice->due_at->format('d F Y') }}</p>
        @endif
        @if ($invoice->paid_at)
            <p class="label" style="margin-top:8px;">Paid At</p>
            <p class="value">{{ $invoice->paid_at->format('d F Y H:i') }}</p>
        @endif
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Description</th>
            <th>Plan</th>
            <th class="text-right">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $invoice->description ?: 'Subscription' }}</td>
            <td>{{ $invoice->plan?->name ?? '—' }}</td>
            <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
        </tr>
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>Subtotal</td>
        <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
    </tr>
    <tr>
        <td>Tax</td>
        <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->tax ?? 0, 2) }}</td>
    </tr>
    <tr class="grand-total">
        <td>Total</td>
        <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
    </tr>
</table>

<div class="footer">
    <p>{{ config('app.name') }} &mdash; Auto-generated invoice &mdash; {{ now()->format('d M Y') }}</p>
    @if ($invoice->gateway_invoice_id)
        <p>Gateway Invoice ID: {{ $invoice->gateway_invoice_id }}</p>
    @endif
</div>
</body>
</html>
