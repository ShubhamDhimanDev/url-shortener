<?php

namespace App\Notifications\Subscriptions;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent 3 days before a trial subscription expires.
 * Delivered via mail and stored in the database.
 */
class TrialEndingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Subscription $subscription)
    {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $planName = $this->subscription->plan?->name ?? 'your current plan';
        $endsAt   = $this->subscription->trial_ends_at?->toFormattedDateString() ?? 'soon';

        return (new MailMessage)
            ->subject('Your free trial ends in 3 days')
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your free trial of **{$planName}** will expire on **{$endsAt}**.")
            ->line('Upgrade now to keep uninterrupted access to all your links and analytics.')
            ->action('Upgrade My Plan', $this->upgradeUrl())
            ->line('If you have any questions, feel free to reply to this email.')
            ->salutation('The ' . config('app.name') . ' Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'trial_ending',
            'plan_name'      => $this->subscription->plan?->name,
            'trial_ends_at'  => $this->subscription->trial_ends_at?->toISOString(),
            'upgrade_url'    => $this->upgradeUrl(),
        ];
    }

    private function upgradeUrl(): string
    {
        try {
            return route('app.billing.plans');
        } catch (\Throwable) {
            return url('/app/billing/plans');
        }
    }
}
