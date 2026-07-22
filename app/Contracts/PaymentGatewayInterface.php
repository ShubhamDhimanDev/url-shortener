<?php

namespace App\Contracts;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Create a customer in the payment gateway.
     *
     * @param  User|Team  $billable
     * @return array{id: string, gateway_customer_id: string, raw: array}
     */
    public function createCustomer(User|Team $billable): array;

    /**
     * Create a subscription in the payment gateway.
     *
     * @param  Subscription  $subscription
     * @param  Plan  $plan
     * @param  array<string, mixed>  $options
     * @return array{gateway_subscription_id: string, status: string, raw: array}
     */
    public function createSubscription(Subscription $subscription, Plan $plan, array $options = []): array;

    /**
     * Cancel a subscription in the payment gateway.
     *
     * @param  Subscription  $subscription
     * @return bool
     */
    public function cancelSubscription(Subscription $subscription): bool;

    /**
     * Retrieve the status of a subscription from the gateway.
     *
     * @param  string  $gatewaySubscriptionId
     * @return string  One of: trialing, active, past_due, cancelled, expired
     */
    public function getSubscriptionStatus(string $gatewaySubscriptionId): string;

    /**
     * Create an invoice in the payment gateway.
     *
     * @param  array<string, mixed>  $data
     * @return array{gateway_invoice_id: string, status: string, raw: array}
     */
    public function createInvoice(array $data): array;

    /**
     * Retrieve an invoice from the payment gateway.
     *
     * @param  string  $gatewayInvoiceId
     * @return array<string, mixed>
     */
    public function getInvoice(string $gatewayInvoiceId): array;

    /**
     * Process an incoming webhook payload.
     *
     * @param  Request  $request
     * @return void
     */
    public function handleWebhook(Request $request): void;

    /**
     * Verify the webhook signature to ensure the request is authentic.
     *
     * @param  Request  $request
     * @return bool
     */
    public function verifyWebhookSignature(Request $request): bool;
}
