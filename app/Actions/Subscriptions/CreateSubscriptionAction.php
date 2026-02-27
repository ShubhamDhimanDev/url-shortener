<?php

namespace App\Actions\Subscriptions;

use App\Contracts\PaymentGatewayInterface;
use App\Events\Subscriptions\SubscriptionCreated;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Carbon;

class CreateSubscriptionAction
{
    public function __construct(private readonly PaymentGatewayInterface $gateway) {}

    /**
     * Create a subscription for a User or Team on the given plan.
     *
     * @param  User|Team  $entity
     * @param  array      $options  Gateway-specific options (e.g. payment_method_id)
     */
    public function execute(User|Team $entity, Plan $plan, array $options = []): Subscription
    {
        // Create / retrieve gateway customer (reuses existing customer when upgrading).
        $gatewayData = $this->gateway->createCustomer($entity);

        // Cancel any existing active subscription first
        $existing = $entity->subscription;
        if ($existing && in_array($existing->status, ['active', 'trialing'])) {
            $this->gateway->cancelSubscription($existing);
            $existing->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }

        // Determine trial period
        $trialEndsAt = $plan->trial_days > 0
            ? Carbon::now()->addDays($plan->trial_days)
            : null;

        $status = $trialEndsAt ? 'trialing' : 'active';

        // Create subscription record
        $subscription = Subscription::create([
            'subscribable_type'       => get_class($entity),
            'subscribable_id'         => $entity->id,
            'plan_id'                 => $plan->id,
            'gateway'                 => config('payment.default'),
            'gateway_customer_id'     => $gatewayData['gateway_customer_id'] ?? null,
            'status'                  => $status,
            'trial_ends_at'           => $trialEndsAt,
            'current_period_start'    => Carbon::now(),
            'current_period_end'      => Carbon::now()->addMonth(),
        ]);

        // Fire event — listener will send welcome/confirmation email
        event(new SubscriptionCreated($subscription));

        return $subscription;
    }
}
