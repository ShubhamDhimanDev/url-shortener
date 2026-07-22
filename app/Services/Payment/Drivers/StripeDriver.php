<?php

namespace App\Services\Payment\Drivers;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Stripe Payment Driver — stub for future implementation.
 *
 * Install: composer require stripe/stripe-php
 */
class StripeDriver implements PaymentGatewayInterface
{
    public function __construct()
    {
        // TODO: Initialize Stripe SDK
        // \Stripe\Stripe::setApiKey(config('payment.gateways.stripe.secret'));
    }

    public function createCustomer(User|Team $billable): array
    {
        throw new RuntimeException('StripeDriver::createCustomer() is not yet implemented.');
    }

    public function createSubscription(Subscription $subscription, Plan $plan, array $options = []): array
    {
        throw new RuntimeException('StripeDriver::createSubscription() is not yet implemented.');
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        throw new RuntimeException('StripeDriver::cancelSubscription() is not yet implemented.');
    }

    public function getSubscriptionStatus(string $gatewaySubscriptionId): string
    {
        throw new RuntimeException('StripeDriver::getSubscriptionStatus() is not yet implemented.');
    }

    public function createInvoice(array $data): array
    {
        throw new RuntimeException('StripeDriver::createInvoice() is not yet implemented.');
    }

    public function getInvoice(string $gatewayInvoiceId): array
    {
        throw new RuntimeException('StripeDriver::getInvoice() is not yet implemented.');
    }

    public function handleWebhook(Request $request): void
    {
        throw new RuntimeException('StripeDriver::handleWebhook() is not yet implemented.');
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        throw new RuntimeException('StripeDriver::verifyWebhookSignature() is not yet implemented.');
    }
}
