<?php

namespace App\Http\Controllers\App;

use App\Actions\Subscriptions\CancelSubscriptionAction;
use App\Actions\Subscriptions\CreateSubscriptionAction;
use App\Contracts\PaymentGatewayInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriptions\CreateSubscriptionRequest;
use App\Models\Invoice;
use App\Models\Plan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        private readonly CreateSubscriptionAction $createSubscription,
        private readonly CancelSubscriptionAction $cancelSubscription,
        private readonly PaymentGatewayInterface  $gateway,
    ) {}

    // ─── Billing overview ─────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user   = $request->user();
        $entity = $this->resolveEntity($user);

        $subscription   = $entity->subscription;
        $activePlan     = $subscription?->plan;
        $paymentMethods = $entity->paymentMethods()->orderByDesc('is_default')->get();

        $invoices = Invoice::query()
            ->where('subscribable_type', get_class($entity))
            ->where('subscribable_id', $entity->id)
            ->latest()
            ->paginate(10);

        return view('app.billing.index', compact(
            'subscription',
            'activePlan',
            'paymentMethods',
            'invoices',
            'entity',
        ));
    }

    // ─── Plans comparison ─────────────────────────────────────────────────────

    public function plans(Request $request): View
    {
        $plans = Plan::where('is_active', true)
            ->where('is_public', true)
            ->with('features')
            ->orderBy('sort_order')
            ->get();

        $entity       = $this->resolveEntity($request->user());
        $currentPlan  = $entity->subscription?->plan;

        return view('app.billing.plans', compact('plans', 'currentPlan', 'entity'));
    }

    // ─── Subscribe ────────────────────────────────────────────────────────────

    public function subscribe(CreateSubscriptionRequest $request): RedirectResponse
    {
        $user   = $request->user();
        $entity = $this->resolveEntity($user);

        $plan = Plan::findOrFail($request->input('plan_id'));

        $this->createSubscription->execute($entity, $plan, $request->validated());

        return redirect()->route('app.billing.index')
            ->with('success', 'Subscription started! Welcome to ' . $plan->name . '.');
    }

    // ─── Cancel subscription ──────────────────────────────────────────────────

    public function cancel(Request $request): RedirectResponse
    {
        $entity       = $this->resolveEntity($request->user());
        $subscription = $entity->subscription;

        if (! $subscription || $subscription->status === 'cancelled') {
            return back()->with('error', 'No active subscription to cancel.');
        }

        $this->cancelSubscription->execute($subscription);

        return back()->with('success', 'Subscription cancelled. You have access until the end of the billing period.');
    }

    // ─── Invoice download ─────────────────────────────────────────────────────

    public function downloadInvoice(Request $request, string $ulid): Response
    {
        $user   = $request->user();
        $entity = $this->resolveEntity($user);

        $invoice = Invoice::where('ulid', $ulid)
            ->where('subscribable_type', get_class($entity))
            ->where('subscribable_id', $entity->id)
            ->firstOrFail();

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice, 'entity' => $entity]);

        return $pdf->download("invoice-{$invoice->ulid}.pdf");
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function resolveEntity($user)
    {
        // If the user is in a team context and has billing permission on that team, bill the team
        $teamId = session('active_team_id');
        if ($teamId) {
            $membership = $user->teamMemberships()->where('team_id', $teamId)->first();
            if ($membership && in_array($membership->role, ['owner', 'admin'])) {
                return $membership->team;
            }
        }
        return $user;
    }
}
