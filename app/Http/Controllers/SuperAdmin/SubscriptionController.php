<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UpdateSubscriptionRequest;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subscription::with(['plan', 'subscribable'])
            ->withTrashed();

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by plan
        if ($planId = $request->input('plan_id')) {
            $query->where('plan_id', $planId);
        }

        // Filter by gateway
        if ($gateway = $request->input('gateway')) {
            $query->where('gateway', $gateway);
        }

        // Search by gateway subscription ID
        if ($search = $request->input('search')) {
            $query->where('gateway_subscription_id', 'like', "%{$search}%");
        }

        $subscriptions = $query->latest()->paginate(25)->withQueryString();
        $plans         = Plan::orderBy('name')->get();
        $statuses      = ['trialing', 'active', 'past_due', 'cancelled', 'expired'];
        $gateways      = Subscription::distinct()->pluck('gateway');

        return view('super-admin.subscriptions.index', compact(
            'subscriptions', 'plans', 'statuses', 'gateways'
        ));
    }

    public function show(Subscription $subscription): View
    {
        $subscription->loadMissing(['plan.features', 'subscribable', 'invoices' => fn ($q) => $q->latest()->limit(10)]);

        return view('super-admin.subscriptions.show', compact('subscription'));
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $data = $request->only('plan_id', 'status', 'current_period_start', 'current_period_end', 'ends_at');

        // Cast nullable dates
        foreach (['current_period_start', 'current_period_end', 'ends_at'] as $dateField) {
            if (isset($data[$dateField]) && $data[$dateField] === '') {
                $data[$dateField] = null;
            }
        }

        $subscription->update($data);

        return redirect()
            ->route('super-admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription updated.');
    }

    public function cancel(Subscription $subscription): RedirectResponse
    {
        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'ends_at'      => $subscription->current_period_end ?? now(),
        ]);

        return back()->with('success', 'Subscription cancelled.');
    }
}
