<?php

namespace App\Notifications\Subscriptions;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when a subscription is created or activated (including trial start).
 * Includes plan details and a link to the first invoice when available.
 */
class SubscriptionConfirmedNotification extends Notification implements ShouldQueue
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
        $plan         = $this->subscription->plan;
        $planName     = $plan?->name ?? 'your selected plan';
        $periodEnd    = $this->subscription->current_period_end?->toFormattedDateString()
                        ?? $this->subscription->trial_ends_at?->toFormattedDateString()
                        ?? 'N/A';
        $isTrialing   = $this->subscription->status === 'trialing';

        $message = (new MailMessage)
            ->subject('Your ' . config('app.name') . ' subscription is confirmed')
            ->greeting("Hi {$notifiable->name}!")
            ->line($isTrialing
                ? "Your **{$planName}** free trial has started."
                : "Your **{$planName}** subscription has been activated.")
            ->line("Your current billing period runs until **{$periodEnd}**.");

        if (! $isTrialing) {
            $message->action('View Invoice', $this->billingUrl());
        } else {
            $message->action('Explore Features', $this->dashboardUrl());
        }

        return $message->salutation('The ' . config('app.name') . ' Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'              => 'subscription_confirmed',
            'plan_name'         => $this->subscription->plan?->name,
            'status'            => $this->subscription->status,
            'current_period_end'=> $this->subscription->current_period_end?->toISOString(),
            'billing_url'       => $this->billingUrl(),
        ];
    }

    private function billingUrl(): string
    {
        try {
            return route('app.billing.index');
        } catch (\Throwable) {
            return url('/app/billing');
        }
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
