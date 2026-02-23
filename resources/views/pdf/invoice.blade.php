<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Invoice {{ $invoice->invoice_number ?? '#' . $invoice->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background: #fff;
            padding: 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #9a28eb;
            padding-bottom: 24px;
            margin-bottom: 32px;
        }
        .brand { font-size: 22px; font-weight: 800; color: #9a28eb; }
        .brand small { display: block; font-size: 11px; font-weight: 400; color: #64748b; margin-top: 2px; }
        .invoice-meta { text-align: right; }
        .invoice-meta h1 { font-size: 20px; font-weight: 700; text-transform: uppercase; color: #9a28eb; }
        .invoice-meta p { color: #64748b; margin-top: 4px; }
        .invoice-meta .status {
            display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 10px;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 6px;
        }
        .status-paid   { background: #dcfce7; color: #16a34a; }
        .status-pending { background: #fef9c3; color: #ca8a04; }
        .status-failed  { background: #fee2e2; color: #dc2626; }
        .parties {
            display: flex;
            justify-content: space-between;
            margin-bottom: 32px;
            gap: 24px;
        }
        .party { flex: 1; }
        .party-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #9a28eb; font-weight: 700; margin-bottom: 6px; }
        .party p { color: #334155; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead tr { background: #f1f5f9; border-bottom: 1px solid #e2e8f0; }
        th { padding: 10px 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
        td { padding: 12px 12px; border-bottom: 1px solid #f1f5f9; color: #1e293b; }
        .total-section { display: flex; justify-content: flex-end; }
        .total-table { width: 240px; }
        .total-table td { padding: 6px 12px; }
        .total-table .total-row { font-weight: 700; font-size: 14px; border-top: 2px solid #9a28eb; color: #9a28eb; }
        .footer { margin-top: 48px; padding-top: 16px; border-top: 1px solid #e2e8f0; text-align: center; color: #94a3b8; font-size: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <div class="brand">
                {{ config('app.name') }}
                <small>{{ config('app.url') }}</small>
            </div>
        </div>
        <div class="invoice-meta">
            <h1>Invoice</h1>
            <p>{{ $invoice->invoice_number ?? '#' . $invoice->id }}</p>
            <p>{{ $invoice->created_at->format('d M Y') }}</p>
            <span class="status status-{{ $invoice->status }}">{{ $invoice->status }}</span>
        </div>
    </div>

    <div class="parties">
        <div class="party">
            <div class="party-label">From</div>
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>{{ config('app.support_email', 'support@' . parse_url(config('app.url'), PHP_URL_HOST)) }}</p>
        </div>
        <div class="party">
            <div class="party-label">Billed To</div>
            @if($entity instanceof \App\Models\Team)
                <p><strong>{{ $entity->name }}</strong></p>
                <p>Team Account</p>
                @if($entity->owner)
                    <p>{{ $entity->owner->email }}</p>
                @endif
            @else
                <p><strong>{{ $entity->name }}</strong></p>
                <p>{{ $entity->email }}</p>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Period</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $invoice->subscription?->plan?->name ?? 'Subscription' }} — Billing Period</td>
                <td>
                    @if($invoice->billing_period_start && $invoice->billing_period_end)
                        {{ \Carbon\Carbon::parse($invoice->billing_period_start)->format('d M Y') }}
                        – {{ \Carbon\Carbon::parse($invoice->billing_period_end)->format('d M Y') }}
                    @else
                        —
                    @endif
                </td>
                <td style="text-align:right">
                    {{ strtoupper($invoice->currency ?? 'INR') }}
                    {{ number_format(($invoice->amount - ($invoice->tax_amount ?? 0)) / 100, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <table class="total-table">
            <tr>
                <td>Subtotal</td>
                <td style="text-align:right">
                    {{ strtoupper($invoice->currency ?? 'INR') }}
                    {{ number_format(($invoice->amount - ($invoice->tax_amount ?? 0)) / 100, 2) }}
                </td>
            </tr>
            @if(($invoice->tax_amount ?? 0) > 0)
                <tr>
                    <td>Tax (18% GST)</td>
                    <td style="text-align:right">
                        {{ strtoupper($invoice->currency ?? 'INR') }}
                        {{ number_format($invoice->tax_amount / 100, 2) }}
                    </td>
                </tr>
            @endif
            <tr class="total-row">
                <td>Total</td>
                <td style="text-align:right">
                    {{ strtoupper($invoice->currency ?? 'INR') }}
                    {{ number_format($invoice->amount / 100, 2) }}
                </td>
            </tr>
        </table>
    </div>

    @if($invoice->payment_method)
        <p style="margin-top:24px; color:#64748b; font-size:11px;">
            Payment via <strong>{{ ucfirst($invoice->payment_method->type ?? 'online') }}</strong>
            @if($invoice->payment_method->last_four)
                ending in <strong>{{ $invoice->payment_method->last_four }}</strong>
            @endif
        </p>
    @endif

    <div class="footer">
        <p>Thank you for using {{ config('app.name') }}. This is a computer-generated invoice.</p>
        <p>For any billing queries, contact {{ config('app.support_email', 'support@' . parse_url(config('app.url'), PHP_URL_HOST)) }}</p>
    </div>

</body>
</html>
