<?php

namespace App\Notifications\Teams;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * Sent to an invitee when they are invited to join a team.
 * Contains a signed URL that is valid for 48 hours.
 *
 * Usage (anonymous notifiable):
 *   Notification::route('mail', $email)->notify(new TeamInviteNotification($team, $role));
 *
 * Usage (existing User):
 *   $user->notify(new TeamInviteNotification($team, $role));
 */
class TeamInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $queue = 'notifications';

    /** Pre-generated signed accept URL (injected at listener level). */
    private string $acceptUrl;

    public function __construct(
        public readonly Team   $team,
        public readonly string $role,
        public readonly string $inviteeEmail,
        ?string                $acceptUrl = null,
    ) {
        $this->acceptUrl = $acceptUrl ?? $this->buildSignedUrl();
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $roleName = ucfirst($this->role);

        return (new MailMessage)
            ->subject("You've been invited to join {$this->team->name} on " . config('app.name'))
            ->greeting("Hi there!")
            ->line("**{$this->team->name}** has invited you to join their team as a **{$roleName}**.")
            ->line('Click the button below to accept the invitation. This link is valid for **48 hours**.')
            ->action('Accept Invitation', $this->acceptUrl)
            ->line('If you did not expect this invitation, you can safely ignore this email.')
            ->salutation('The ' . config('app.name') . ' Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'         => 'team_invite',
            'team_id'      => $this->team->id,
            'team_name'    => $this->team->name,
            'role'         => $this->role,
            'accept_url'   => $this->acceptUrl,
        ];
    }

    private function buildSignedUrl(): string
    {
        try {
            return \URL::temporarySignedRoute(
                'app.teams.invitations.accept',
                Carbon::now()->addHours(48),
                [
                    'team'  => $this->team->ulid,
                    'email' => $this->inviteeEmail,
                    'role'  => $this->role,
                ]
            );
        } catch (\Throwable) {
            return url('/app/teams/' . $this->team->ulid . '/invitations/accept?email=' . urlencode($this->inviteeEmail));
        }
    }
}
