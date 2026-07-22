<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle an incoming payment gateway webhook.
     *
     * Route: POST /webhooks/{gateway}
     *
     * @param  Request  $request
     * @param  string   $gateway  e.g. 'razorpay' or 'stripe'
     * @return Response
     */
    public function handle(Request $request, string $gateway): Response
    {
        /** @var PaymentGatewayManager $manager */
        $manager = app(PaymentGatewayManager::class);

        // Resolve the driver for this gateway
        try {
            /** @var PaymentGatewayInterface $driver */
            $driver = $manager->driver($gateway);
        } catch (\InvalidArgumentException $e) {
            Log::warning("WebhookController: Unknown gateway [{$gateway}]", [
                'error' => $e->getMessage(),
            ]);

            return response('Invalid gateway', Response::HTTP_NOT_FOUND);
        }

        // Verify signature
        if (! $driver->verifyWebhookSignature($request)) {
            Log::warning("WebhookController: Signature verification failed for [{$gateway}]", [
                'ip' => $request->ip(),
            ]);

            return response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        // Dispatch to gateway-specific handler
        try {
            $driver->handleWebhook($request);
        } catch (\Throwable $e) {
            Log::error("WebhookController: Error handling webhook for [{$gateway}]", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return 500 so the gateway retries
            return response('Internal Server Error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response('OK', Response::HTTP_OK);
    }
}
