<?php

namespace App\Notifications\Subscriptions;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when a subscription successfully renews for another billing period.
 * Acts as a renewal receipt for the subscriber.
 */
class RenewalReceiptNotification extends Notification implements ShouldQueue
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
        $planName  = $this->subscription->plan?->name ?? 'your plan';
        $periodEnd = $this->subscription->current_period_end?->toFormattedDateString() ?? 'N/A';

        return (new MailMessage)
            ->subject('Your ' . config('app.name') . ' subscription has been renewed')
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your **{$planName}** subscription has been successfully renewed.")
            ->line("Your access is now extended until **{$periodEnd}**.")
            ->action('View Invoice', $this->billingUrl())
            ->line('Thank you for staying with us!')
            ->salutation('The ' . config('app.name') . ' Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'               => 'subscription_renewed',
            'plan_name'          => $this->subscription->plan?->name,
            'current_period_end' => $this->subscription->current_period_end?->toISOString(),
            'billing_url'        => $this->billingUrl(),
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
}
