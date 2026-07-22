<?php

namespace App\Listeners\Subscriptions;

use App\Events\Subscriptions\SubscriptionRenewed;
use App\Notifications\Subscriptions\RenewalReceiptNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Sends a renewal receipt email to the subscriber when their
 * subscription successfully renews for another billing period.
 */
class SendRenewalReceiptEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(SubscriptionRenewed $event): void
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

        $notifiable->notify(new RenewalReceiptNotification($subscription));
    }
}
