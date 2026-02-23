<?php

namespace App\Notifications\Subscriptions;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when a subscription is cancelled, informing the user of
 * the date access will expire.
 */
class SubscriptionCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $queue = 'notifications';

    public function __construct(public readonly Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $planName  = $this->subscription->plan?->name ?? 'your plan';
        $endsAt    = $this->subscription->ends_at?->toFormattedDateString()
                     ?? $this->subscription->current_period_end?->toFormattedDateString()
                     ?? 'the end of your current billing period';

        return (new MailMessage)
            ->subject('Your ' . config('app.name') . ' subscription has been cancelled')
            ->greeting("Hi {$notifiable->name},")
            ->line("We've confirmed the cancellation of your **{$planName}** subscription.")
            ->line("You will retain full access until **{$endsAt}**, after which your account will revert to the Free plan.")
            ->line('We\'re sorry to see you go. If you change your mind, you can re-subscribe at any time.')
            ->action('Re-subscribe', $this->plansUrl())
            ->salutation('The ' . config('app.name') . ' Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'      => 'subscription_cancelled',
            'plan_name' => $this->subscription->plan?->name,
            'ends_at'   => $this->subscription->ends_at?->toISOString()
                           ?? $this->subscription->current_period_end?->toISOString(),
            'plans_url' => $this->plansUrl(),
        ];
    }

    private function plansUrl(): string
    {
        try {
            return route('app.billing.plans');
        } catch (\Throwable) {
            return url('/app/billing/plans');
        }
    }
}
