<?php

namespace App\Notifications\Admin;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to all super-admin users when a new user registers on the platform.
 */
class NewUserRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly User $newUser)
    {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New user registered on ' . config('app.name'))
            ->greeting('Hello Admin,')
            ->line("A new user has registered on the platform.")
            ->line("**Name:** {$this->newUser->name}")
            ->line("**Email:** {$this->newUser->email}")
            ->line("**Registered at:** {$this->newUser->created_at?->isoFormat('lll')}")
            ->action('View User', $this->userAdminUrl())
            ->salutation(config('app.name') . ' Platform');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'admin_new_user',
            'user_id'        => $this->newUser->id,
            'user_ulid'      => $this->newUser->ulid,
            'user_name'      => $this->newUser->name,
            'user_email'     => $this->newUser->email,
            'registered_at'  => $this->newUser->created_at?->toISOString(),
            'admin_url'      => $this->userAdminUrl(),
        ];
    }

    private function userAdminUrl(): string
    {
        try {
            return route('super-admin.users.show', $this->newUser->ulid);
        } catch (\Throwable) {
            return url('/super-admin/users/' . $this->newUser->ulid);
        }
    }
}
