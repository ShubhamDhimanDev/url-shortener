<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This value determines the default payment gateway that will be used
    | when resolving the PaymentGatewayInterface out of the container.
    | Supported: "razorpay", "stripe"
    |
    */

    'default' => env('PAYMENT_GATEWAY', 'razorpay'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure the credentials for each payment gateway.
    | These values are read by their respective driver implementations.
    |
    */

    'gateways' => [

        'razorpay' => [
            'key'            => env('RAZORPAY_KEY'),
            'secret'         => env('RAZORPAY_SECRET'),
            'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        ],

        'stripe' => [
            'key'            => env('STRIPE_KEY'),
            'secret'         => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],

    ],

];
