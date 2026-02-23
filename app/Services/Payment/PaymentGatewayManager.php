<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Drivers\RazorpayDriver;
use App\Services\Payment\Drivers\StripeDriver;
use Illuminate\Support\Manager;

/**
 * Resolves the correct payment gateway driver based on the
 * `payment.default` config value (or overridden via driver()).
 *
 * Usage:
 *   app(PaymentGatewayManager::class)->driver()          // uses default
 *   app(PaymentGatewayManager::class)->driver('stripe')  // explicit
 */
class PaymentGatewayManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return config('payment.default', 'razorpay');
    }

    /**
     * Create the Razorpay driver.
     */
    protected function createRazorpayDriver(): PaymentGatewayInterface
    {
        return new RazorpayDriver();
    }

    /**
     * Create the Stripe driver (stub — not yet fully implemented).
     */
    protected function createStripeDriver(): PaymentGatewayInterface
    {
        return new StripeDriver();
    }
}
