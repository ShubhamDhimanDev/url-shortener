<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Invoice::with(['subscribable', 'plan', 'subscription']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($gateway = $request->input('gateway')) {
            $query->where('gateway', $gateway);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('gateway_invoice_id', 'like', "%{$search}%")
                  ->orWhere('gateway_payment_id', 'like', "%{$search}%")
                  ->orWhere('ulid', $search);
            });
        }

        $invoices  = $query->latest()->paginate(25)->withQueryString();
        $statuses  = ['draft', 'open', 'paid', 'void', 'uncollectible'];
        $gateways  = Invoice::distinct()->pluck('gateway')->filter();

        return view('super-admin.invoices.index', compact('invoices', 'statuses', 'gateways'));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->loadMissing(['subscribable', 'plan', 'subscription']);

        return view('super-admin.invoices.show', compact('invoice'));
    }

    public function download(Invoice $invoice): Response
    {
        $invoice->loadMissing(['subscribable', 'plan', 'subscription']);

        $pdf = Pdf::loadView('super-admin.invoices.pdf', compact('invoice'))
            ->setPaper('a4');

        return $pdf->download("invoice-{$invoice->ulid}.pdf");
    }

    public function void(Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->status === 'void', 422, 'Invoice is already voided.');
        abort_if($invoice->status === 'paid', 422, 'Cannot void a paid invoice. Issue a refund instead.');

        $invoice->update(['status' => 'void']);

        return back()->with('success', 'Invoice voided successfully.');
    }
}
