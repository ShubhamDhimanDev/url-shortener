<?php

namespace App\Listeners\Subscriptions;

use App\Events\Subscriptions\SubscriptionCancelled;
use App\Notifications\Subscriptions\SubscriptionCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Sends a cancellation confirmation email to the subscriber when
 * a subscription is cancelled.
 */
class SendCancellationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(SubscriptionCancelled $event): void
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

        $notifiable->notify(new SubscriptionCancelledNotification($subscription));
    }
}
