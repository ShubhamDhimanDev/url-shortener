<?php

namespace App\Notifications\Auth;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a newly registered user as a welcome message.
 * Delivered via mail and stored in the database notification inbox.
 */
class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $queue = 'notifications';

    public function __construct(public readonly User $user) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . '!')
            ->greeting("Hi {$notifiable->name}!")
            ->line('Thank you for joining ' . config('app.name') . '. We\'re thrilled to have you on board.')
            ->line('You can start shortening links right away — no credit card required.')
            ->action('Go to Dashboard', $this->dashboardUrl())
            ->line('If you have any questions, just reply to this email — we\'re always happy to help.')
            ->salutation('The ' . config('app.name') . ' Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'welcome',
            'message'       => 'Welcome to ' . config('app.name') . '! Start shortening links now.',
            'dashboard_url' => $this->dashboardUrl(),
        ];
    }

    private function dashboardUrl(): string
    {
        try {
            return route('app.dashboard');
        } catch (\Throwable) {
            return url('/app/dashboard');
        }
    }
}
