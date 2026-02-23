<?php

namespace App\Notifications\Subscriptions;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when a payment attempt fails for an active subscription.
 * Prompts the user to update their payment method to avoid interruption.
 */
class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $queue = 'notifications';

    public function __construct(
        public readonly Subscription $subscription,
        public readonly array        $payload = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $planName   = $this->subscription->plan?->name ?? 'your plan';
        $amount     = isset($this->payload['amount'])
            ? number_format($this->payload['amount'] / 100, 2) . ' ' . strtoupper($this->payload['currency'] ?? 'INR')
            : null;

        $message = (new MailMessage)
            ->subject('Action required: Payment failed for your ' . config('app.name') . ' subscription')
            ->greeting("Hi {$notifiable->name},")
            ->line("We were unable to process the payment for your **{$planName}** subscription." .
                   ($amount ? " Amount attempted: **{$amount}**." : ''))
            ->line('To avoid any disruption to your service, please update your payment method.')
            ->action('Update Payment Method', $this->billingUrl())
            ->line('If you believe this is an error, please contact our support team.')
            ->salutation('The ' . config('app.name') . ' Team');

        return $message;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'payment_failed',
            'plan_name'      => $this->subscription->plan?->name,
            'amount'         => $this->payload['amount'] ?? null,
            'currency'       => $this->payload['currency'] ?? null,
            'gateway_error'  => $this->payload['error'] ?? null,
            'billing_url'    => $this->billingUrl(),
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
