<?php

namespace App\Actions\Subscriptions;

use App\Contracts\PaymentGatewayInterface;
use App\Events\Subscriptions\SubscriptionCancelled;
use App\Models\Subscription;

class CancelSubscriptionAction
{
    public function __construct(private readonly PaymentGatewayInterface $gateway) {}

    public function execute(Subscription $subscription): void
    {
        if ($subscription->gateway_subscription_id) {
            $this->gateway->cancelSubscription($subscription);
        }

        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'ends_at'      => $subscription->current_period_end,
        ]);

        // Observer on Subscription will also fire SubscriptionCancelled,
        // but we fire explicitly here for immediate action.
        event(new SubscriptionCancelled($subscription));
    }
}
