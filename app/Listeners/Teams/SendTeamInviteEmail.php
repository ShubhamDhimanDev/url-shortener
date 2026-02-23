<?php

namespace App\Listeners\Teams;

use App\Events\Teams\MemberInvited;
use App\Notifications\Teams\TeamInviteNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Sends a team invitation email when a member is invited to a team.
 *
 * Handles both existing users and brand-new invitees via anonymous notifiables.
 * The signed URL (valid 48 hours) is embedded in the notification.
 */
class SendTeamInviteEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(MemberInvited $event): void
    {
        $notification = new TeamInviteNotification(
            team:         $event->team,
            role:         $event->role,
            inviteeEmail: $event->email,
        );

        // Check if the invitee already has an account so we also store a DB notification.
        $existingUser = \App\Models\User::where('email', $event->email)->first();

        if ($existingUser) {
            $existingUser->notify($notification);
        } else {
            // Anonymous notifiable — only mail channel is used.
            Notification::route('mail', $event->email)->notify($notification);
        }
    }
}
