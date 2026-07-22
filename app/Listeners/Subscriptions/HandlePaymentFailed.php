<?php

namespace App\Listeners\Subscriptions;

use App\Events\Subscriptions\PaymentFailed;
use App\Notifications\Subscriptions\PaymentFailedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Sends a payment-failed notification to the subscriber when a
 * payment attempt is rejected by the gateway.
 */
class HandlePaymentFailed implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(PaymentFailed $event): void
    {
        $subscription = $event->subscription;
        $subscribable = $subscription->subscribable;

        if (! $subscribable) {
            return;
        }

        $notifiable = $subscribable instanceof \App\Models\Team
            ? $subscribable->owner
            : $subscribable;

        if (! $notifiable) {
            return;
        }

        $notifiable->notify(new PaymentFailedNotification($subscription, $event->payload));
    }
}
