<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserRegistered;
use App\Models\User;
use App\Notifications\Admin\NewUserRegisteredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Notifies all super-admin users when a new user registers on the platform.
 */
class NotifyAdminNewUser implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(UserRegistered $event): void
    {
        $notification = new NewUserRegisteredNotification($event->user);

        User::role('super_admin')
            ->get()
            ->each(fn (User $admin) => $admin->notify($notification));
    }
}
