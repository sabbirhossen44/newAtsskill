<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Env-backed gateway configuration. All values are env() placeholders
    | only — no real keys. The live source of truth at runtime is the
    | `payment_gateways` table (App\Services\PaymentService reads keys
    | like `stripe_status`, `stripe_key` from it); this file is the
    | env-backed fallback / display list for the admin panel and checkout.
    |
    | Gateway keys here match PaymentService::GATEWAYS plus the extra
    | display/config entries the task requires: `free` (no charge) and
    | the live merchants nagad / rocket / sslcommerz, which the old app
    | offered but has no SDK wired in this rebuild yet (see 'nagad',
    | 'rocket', 'sslcommerz' below — config entries only, driver null).
    |
    */

    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'stripe'),

    'currency' => env('PAYMENT_CURRENCY', 'BDT'),

    /*
    |--------------------------------------------------------------------------
    | Gateway list
    |--------------------------------------------------------------------------
    |
    | `driver` is the SDK/package used when one is installed, or null when
    | the gateway is config/display only. `enabled` is the env default;
    | the DB `payment_gateways` table overrides it at runtime.
    |
    */

    'gateways' => [

        'stripe' => [
            'driver' => 'stripe/stripe-php',
            'enabled' => env('STRIPE_ENABLED', false),
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],

        'paypal' => [
            'driver' => 'srmklive/paypal',
            'enabled' => env('PAYPAL_ENABLED', false),
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'mode' => env('PAYPAL_MODE', 'sandbox'),
        ],

        'razorpay' => [
            'driver' => 'razorpay/razorpay',
            'enabled' => env('RAZORPAY_ENABLED', false),
            'key' => env('RAZORPAY_KEY'),
            'secret' => env('RAZORPAY_SECRET'),
        ],

        'mollie' => [
            'driver' => 'mollie/laravel-mollie',
            'enabled' => env('MOLLIE_ENABLED', false),
            'key' => env('MOLLIE_KEY'),
        ],

        'instamojo' => [
            'driver' => null,
            'enabled' => env('INSTAMOJO_ENABLED', false),
            'api_key' => env('INSTAMOJO_API_KEY'),
            'auth_token' => env('INSTAMOJO_AUTH_TOKEN'),
            'sandbox' => env('INSTAMOJO_SANDBOX', true),
        ],

        'uddoktapay' => [
            'driver' => null,
            'enabled' => env('UDDOKTAPAY_ENABLED', false),
            'api_key' => env('UDDOKTAPAY_API_KEY'),
            'base_url' => env('UDDOKTAPAY_BASE_URL'),
        ],

        'flutterwave' => [
            'driver' => null,
            'enabled' => env('FLUTTERWAVE_ENABLED', false),
            'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
            'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
            'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
        ],

        'paystack' => [
            'driver' => null,
            'enabled' => env('PAYSTACK_ENABLED', false),
            'public_key' => env('PAYSTACK_PUBLIC_KEY'),
            'secret_key' => env('PAYSTACK_SECRET_KEY'),
        ],

        'bkash' => [
            'driver' => 'karim007/laravel-bkash-tokenize',
            'enabled' => env('BKASH_ENABLED', false),
            'app_key' => env('BKASH_APP_KEY'),
            'app_secret' => env('BKASH_APP_SECRET'),
            'username' => env('BKASH_USERNAME'),
            'password' => env('BKASH_PASSWORD'),
            'sandbox' => env('BKASH_SANDBOX', true),
        ],

        'bank' => [
            'driver' => null,
            'enabled' => env('BANK_ENABLED', false),
            'account_name' => env('BANK_ACCOUNT_NAME'),
            'account_number' => env('BANK_ACCOUNT_NUMBER'),
            'bank_name' => env('BANK_NAME'),
            'branch' => env('BANK_BRANCH'),
        ],

        'free' => [
            'driver' => null,
            'enabled' => true,
        ],

        // Live merchants offered by the old site. Config/display entries
        // only for now: no SDK is wired in this rebuild yet, so enabling
        // one of these in the admin panel without a driver will return
        // PaymentService's `not_configured` result. Wire a driver (or a
        // manual-payment flow like `bank`) before enabling in production.
        'nagad' => [
            'driver' => null,
            'enabled' => env('NAGAD_ENABLED', false),
            'merchant_id' => env('NAGAD_MERCHANT_ID'),
            'merchant_number' => env('NAGAD_MERCHANT_NUMBER'),
            'public_key' => env('NAGAD_PUBLIC_KEY'),
            'private_key' => env('NAGAD_PRIVATE_KEY'),
            'sandbox' => env('NAGAD_SANDBOX', true),
        ],

        'rocket' => [
            'driver' => null,
            'enabled' => env('ROCKET_ENABLED', false),
            'merchant_number' => env('ROCKET_MERCHANT_NUMBER'),
            'api_key' => env('ROCKET_API_KEY'),
            'sandbox' => env('ROCKET_SANDBOX', true),
        ],

        'sslcommerz' => [
            'driver' => null,
            'enabled' => env('SSLCOMMERZ_ENABLED', false),
            'store_id' => env('SSLCOMMERZ_STORE_ID'),
            'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
            'sandbox' => env('SSLCOMMERZ_SANDBOX', true),
        ],

    ],

];
