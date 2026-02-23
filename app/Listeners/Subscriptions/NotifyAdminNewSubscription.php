<?php

namespace App\Listeners\Subscriptions;

use App\Events\Subscriptions\SubscriptionCreated;
use App\Models\User;
use App\Notifications\Admin\NewSubscriptionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Notifies all super-admin users when a new subscription is created so they
 * can track MRR growth in real-time.
 */
class NotifyAdminNewSubscription implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(SubscriptionCreated $event): void
    {
        $notification = new NewSubscriptionNotification($event->subscription);

        User::role('super_admin')
            ->get()
            ->each(fn (User $admin) => $admin->notify($notification));
    }
}
