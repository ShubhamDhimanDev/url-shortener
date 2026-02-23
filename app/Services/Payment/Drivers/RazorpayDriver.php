<?php

namespace App\Services\Payment\Drivers;

use App\Contracts\PaymentGatewayInterface;
use App\Events\Subscriptions\PaymentFailed;
use App\Events\Subscriptions\SubscriptionCancelled;
use App\Events\Subscriptions\SubscriptionCreated;
use App\Events\Subscriptions\SubscriptionRenewed;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayDriver implements PaymentGatewayInterface
{
    protected Api $api;

    public function __construct()
    {
        $config = config('payment.gateways.razorpay');

        $this->api = new Api($config['key'], $config['secret']);
    }

    /**
     * Create a Razorpay customer for a User or Team.
     *
     * @param  User|Team  $billable
     * @return array{id: string, gateway_customer_id: string, raw: array}
     */
    public function createCustomer(User|Team $billable): array
    {
        $name = $billable instanceof Team ? $billable->name : $billable->name;
        $email = $billable instanceof Team
            ? ($billable->owner?->email ?? '')
            : $billable->email;

        $customer = $this->api->customer->create([
            'name'    => $name,
            'email'   => $email,
            'contact' => '',
            'notes'   => [
                'billable_type' => get_class($billable),
                'billable_id'   => $billable->id,
                'ulid'          => $billable->ulid,
            ],
        ]);

        return [
            'id'                  => $customer->id,
            'gateway_customer_id' => $customer->id,
            'raw'                 => $customer->toArray(),
        ];
    }

    /**
     * Create a Razorpay subscription.
     *
     * @param  Subscription  $subscription
     * @param  Plan  $plan
     * @param  array<string, mixed>  $options
     * @return array{gateway_subscription_id: string, status: string, raw: array}
     */
    public function createSubscription(Subscription $subscription, Plan $plan, array $options = []): array
    {
        $interval       = $options['interval'] ?? 'monthly'; // 'monthly' or 'yearly'
        $totalCount     = $options['total_count'] ?? 120;    // number of billing cycles
        $customerId     = $options['gateway_customer_id'] ?? null;
        $planId         = $options['razorpay_plan_id'] ?? null;

        if (! $planId) {
            // Auto-create / look up a Razorpay plan based on our plan model
            $planId = $this->resolveRazorpayPlanId($plan, $interval);
        }

        $payload = [
            'plan_id'     => $planId,
            'total_count' => $totalCount,
            'quantity'    => 1,
            'notes'       => [
                'subscription_ulid' => $subscription->ulid,
                'plan_slug'         => $plan->slug,
            ],
        ];

        if ($customerId) {
            $payload['customer_id'] = $customerId;
        }

        if ($subscription->trial_ends_at) {
            $payload['start_at'] = $subscription->trial_ends_at->timestamp;
        }

        $razorpaySubscription = $this->api->subscription->create($payload);

        return [
            'gateway_subscription_id' => $razorpaySubscription->id,
            'status'                  => $this->normalizeStatus($razorpaySubscription->status),
            'raw'                     => $razorpaySubscription->toArray(),
        ];
    }

    /**
     * Cancel a Razorpay subscription.
     *
     * @param  Subscription  $subscription
     * @return bool
     */
    public function cancelSubscription(Subscription $subscription): bool
    {
        try {
            $this->api->subscription->fetch($subscription->gateway_subscription_id)->cancel([
                'cancel_at_cycle_end' => 1,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('RazorpayDriver: cancelSubscription failed', [
                'subscription_id'            => $subscription->id,
                'gateway_subscription_id'    => $subscription->gateway_subscription_id,
                'error'                      => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get the normalized subscription status from Razorpay.
     *
     * @param  string  $gatewaySubscriptionId
     * @return string
     */
    public function getSubscriptionStatus(string $gatewaySubscriptionId): string
    {
        $sub = $this->api->subscription->fetch($gatewaySubscriptionId);

        return $this->normalizeStatus($sub->status);
    }

    /**
     * Create a Razorpay invoice.
     *
     * @param  array<string, mixed>  $data
     * @return array{gateway_invoice_id: string, status: string, raw: array}
     */
    public function createInvoice(array $data): array
    {
        $invoice = $this->api->invoice->create([
            'type'        => $data['type'] ?? 'invoice',
            'description' => $data['description'] ?? '',
            'customer_id' => $data['gateway_customer_id'] ?? null,
            'amount'      => $data['amount'] ?? 0,           // in paise
            'currency'    => $data['currency'] ?? 'INR',
            'expire_by'   => $data['expire_by'] ?? now()->addDays(7)->timestamp,
        ]);

        return [
            'gateway_invoice_id' => $invoice->id,
            'status'             => $invoice->status,
            'raw'                => $invoice->toArray(),
        ];
    }

    /**
     * Retrieve a Razorpay invoice.
     *
     * @param  string  $gatewayInvoiceId
     * @return array<string, mixed>
     */
    public function getInvoice(string $gatewayInvoiceId): array
    {
        $invoice = $this->api->invoice->fetch($gatewayInvoiceId);

        return $invoice->toArray();
    }

    /**
     * Verify the Razorpay webhook signature.
     *
     * @param  Request  $request
     * @return bool
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $webhookSecret = config('payment.gateways.razorpay.webhook_secret');

        if (! $webhookSecret) {
            Log::warning('RazorpayDriver: No webhook secret configured — skipping signature check.');

            return false;
        }

        try {
            $this->api->utility->verifyWebhookSignature(
                $request->getContent(),
                $request->header('X-Razorpay-Signature', ''),
                $webhookSecret
            );

            return true;
        } catch (SignatureVerificationError $e) {
            Log::warning('RazorpayDriver: Webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle incoming Razorpay webhook and dispatch domain events.
     *
     * @param  Request  $request
     * @return void
     */
    public function handleWebhook(Request $request): void
    {
        $payload = $request->json()->all();
        $event   = $payload['event'] ?? null;

        Log::info('RazorpayDriver: Webhook received', ['event' => $event]);

        match ($event) {
            'subscription.activated'  => $this->handleSubscriptionActivated($payload),
            'subscription.charged'    => $this->handleSubscriptionCharged($payload),
            'subscription.cancelled'  => $this->handleSubscriptionCancelled($payload),
            'subscription.completed'  => $this->handleSubscriptionCompleted($payload),
            'subscription.halted'     => $this->handleSubscriptionHalted($payload),
            'payment.failed'          => $this->handlePaymentFailed($payload),
            default                   => Log::info('RazorpayDriver: Unhandled webhook event', ['event' => $event]),
        };
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Resolve or create a Razorpay plan and return its ID.
     */
    private function resolveRazorpayPlanId(Plan $plan, string $interval): string
    {
        // Look up meta stored on the plan (stored in plan_features or a dedicated column)
        $key    = "razorpay_plan_id_{$interval}";
        $cached = $plan->features()->where('feature_key', $key)->value('feature_value');

        if ($cached) {
            return $cached;
        }

        // Create the plan on Razorpay
        $amount = $interval === 'yearly'
            ? ($plan->price_yearly * 100)   // convert to paise
            : ($plan->price_monthly * 100);

        $razorpayPlan = $this->api->plan->create([
            'period'   => $interval === 'yearly' ? 'yearly' : 'monthly',
            'interval' => 1,
            'item'     => [
                'name'     => "{$plan->name} ({$interval})",
                'amount'   => (int) $amount,
                'currency' => $plan->currency ?? 'INR',
            ],
        ]);

        return $razorpayPlan->id;
    }

    /**
     * Normalize Razorpay subscription statuses to our internal enum values.
     */
    private function normalizeStatus(string $razorpayStatus): string
    {
        return match ($razorpayStatus) {
            'created', 'authenticated' => 'trialing',
            'active'                   => 'active',
            'pending', 'halted'        => 'past_due',
            'cancelled'                => 'cancelled',
            'completed', 'expired'     => 'expired',
            default                    => 'active',
        };
    }

    private function handleSubscriptionActivated(array $payload): void
    {
        $gatewaySubId = $payload['payload']['subscription']['entity']['id'] ?? null;
        if (! $gatewaySubId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $gatewaySubId)->first();
        if (! $subscription) {
            return;
        }

        $entity = $payload['payload']['subscription']['entity'];

        $subscription->update([
            'status'               => 'active',
            'current_period_start' => isset($entity['current_start']) ? \Carbon\Carbon::createFromTimestamp($entity['current_start']) : now(),
            'current_period_end'   => isset($entity['current_end']) ? \Carbon\Carbon::createFromTimestamp($entity['current_end']) : null,
        ]);

        SubscriptionCreated::dispatch($subscription);
    }

    private function handleSubscriptionCharged(array $payload): void
    {
        $gatewaySubId = $payload['payload']['subscription']['entity']['id'] ?? null;
        if (! $gatewaySubId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $gatewaySubId)->first();
        if (! $subscription) {
            return;
        }

        $entity = $payload['payload']['subscription']['entity'];

        $subscription->update([
            'status'               => 'active',
            'current_period_start' => isset($entity['current_start']) ? \Carbon\Carbon::createFromTimestamp($entity['current_start']) : now(),
            'current_period_end'   => isset($entity['current_end']) ? \Carbon\Carbon::createFromTimestamp($entity['current_end']) : null,
        ]);

        SubscriptionRenewed::dispatch($subscription);
    }

    private function handleSubscriptionCancelled(array $payload): void
    {
        $gatewaySubId = $payload['payload']['subscription']['entity']['id'] ?? null;
        if (! $gatewaySubId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $gatewaySubId)->first();
        if (! $subscription) {
            return;
        }

        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        SubscriptionCancelled::dispatch($subscription);
    }

    private function handleSubscriptionCompleted(array $payload): void
    {
        $gatewaySubId = $payload['payload']['subscription']['entity']['id'] ?? null;
        if (! $gatewaySubId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $gatewaySubId)->first();
        if ($subscription) {
            $subscription->update(['status' => 'expired']);
        }
    }

    private function handleSubscriptionHalted(array $payload): void
    {
        $gatewaySubId = $payload['payload']['subscription']['entity']['id'] ?? null;
        if (! $gatewaySubId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $gatewaySubId)->first();
        if ($subscription) {
            $subscription->update(['status' => 'past_due']);
            PaymentFailed::dispatch($subscription, $payload);
        }
    }

    private function handlePaymentFailed(array $payload): void
    {
        $gatewaySubId = $payload['payload']['payment']['entity']['subscription_id'] ?? null;
        if (! $gatewaySubId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $gatewaySubId)->first();
        if ($subscription) {
            $subscription->update(['status' => 'past_due']);
            PaymentFailed::dispatch($subscription, $payload);
        }
    }
}
