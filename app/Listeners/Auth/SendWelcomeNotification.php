<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserRegistered;
use App\Notifications\Auth\WelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Sends a welcome notification to a newly registered user.
 */
class SendWelcomeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(UserRegistered $event): void
    {
        $event->user->notify(new WelcomeNotification($event->user));
    }
}
