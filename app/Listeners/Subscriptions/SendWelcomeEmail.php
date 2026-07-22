<?php

namespace App\Listeners\Subscriptions;

use App\Events\Subscriptions\SubscriptionCreated;
use App\Notifications\Subscriptions\SubscriptionConfirmedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Sends a subscription confirmation (welcome) email to the subscriber
 * when a new subscription is created or a trial starts.
 */
class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(SubscriptionCreated $event): void
    {
        $subscription = $event->subscription;
        $subscribable = $subscription->subscribable;

        if (! $subscribable) {
            return;
        }

        // For team subscriptions notify the team owner; for user subscriptions notify the user.
        $notifiable = $subscribable instanceof \App\Models\Team
            ? $subscribable->owner
            : $subscribable;

        if (! $notifiable) {
            return;
        }

        $notifiable->notify(new SubscriptionConfirmedNotification($subscription));
    }
}
