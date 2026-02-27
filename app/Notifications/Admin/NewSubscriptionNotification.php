<?php

namespace App\Notifications\Admin;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to all super-admin users when a new subscription is created on the platform.
 */
class NewSubscriptionNotification extends Notification implements ShouldQueue
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
        $subscribable = $this->subscription->subscribable;
        $ownerName    = $subscribable?->name ?? 'Unknown';
        $planName     = $this->subscription->plan?->name ?? 'Unknown plan';
        $gateway      = ucfirst($this->subscription->gateway);
        $status       = ucfirst($this->subscription->status);

        return (new MailMessage)
            ->subject("New subscription created on " . config('app.name'))
            ->greeting('Hello Admin,')
            ->line("A new **{$planName}** subscription has been created.")
            ->line("**Subscriber:** {$ownerName}")
            ->line("**Gateway:** {$gateway}")
            ->line("**Status:** {$status}")
            ->action('View Subscription', $this->subscriptionAdminUrl())
            ->salutation(config('app.name') . ' Platform');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'admin_new_subscription',
            'subscription_id' => $this->subscription->id,
            'subscription_ulid'=> $this->subscription->ulid,
            'plan_name'       => $this->subscription->plan?->name,
            'gateway'         => $this->subscription->gateway,
            'status'          => $this->subscription->status,
            'admin_url'       => $this->subscriptionAdminUrl(),
        ];
    }

    private function subscriptionAdminUrl(): string
    {
        try {
            return route('super-admin.subscriptions.show', $this->subscription->ulid);
        } catch (\Throwable) {
            return url('/super-admin/subscriptions/' . $this->subscription->ulid);
        }
    }
}
