<?php

namespace App\Listeners\Links;

use App\Events\Links\LinkCreated;
use App\Models\User;
use App\Notifications\Links\LinkCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Sends a LinkCreatedNotification to the link owner.
 *
 * Opt-in: the notification is only dispatched when the owning user
 * has the 'notify_link_created' setting enabled.  This defaults to false,
 * keeping the inbox quiet for users who haven't opted in.
 */
class SendLinkCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(LinkCreated $event): void
    {
        $link = $event->link;
        $user = $link->user;

        if (! $user instanceof User) {
            return;
        }

        // Opt-in guard: check a per-user setting stored in the settings table.
        // Defaults to false so new users don't get flooded.
        $optedIn = (bool) \App\Models\Setting::query()
            ->where('group', 'notifications')
            ->where('key', "user_{$user->id}_link_created")
            ->value('value');

        if (! $optedIn) {
            return;
        }

        $user->notify(new LinkCreatedNotification($link));
    }
}
