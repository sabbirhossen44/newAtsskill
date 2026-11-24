<?php

namespace App\Services;

use App\Models\BasicPayment;
use App\Models\Order;
use App\Models\PaymentGateway;

class PaymentService
{
    /**
     * Gateways present (by key prefix) in the old payment_gateways /
     * basic_payments tables. Values are NEVER hard-coded here: every
     * credential comes from the database or env() placeholders.
     */
    public const GATEWAYS = [
        'stripe', 'paypal', 'razorpay', 'flutterwave', 'paystack',
        'mollie', 'instamojo', 'uddoktapay', 'bank',
    ];

    public static function gatewayConfig(string $gateway): array
    {
        $prefix = strtolower($gateway).'_';

        $config = PaymentGateway::query()
            ->where('key', 'like', $prefix.'%')
            ->pluck('value', 'key')
            ->all();

        $basic = BasicPayment::query()
            ->where('key', 'like', $prefix.'%')
            ->pluck('value', 'key')
            ->all();

        return array_merge($basic, $config);
    }

    public static function isEnabled(string $gateway): bool
    {
        $config = static::gatewayConfig($gateway);

        $status = $config[strtolower($gateway).'_status']
            ?? $config[strtolower($gateway).'_active']
            ?? null;

        return in_array($status, ['1', 'active', 'enabled', true, 1], true);
    }

    public static function supportedGateways(): array
    {
        return array_values(array_filter(
            static::GATEWAYS,
            fn ($gateway) => static::isEnabled($gateway)
        ));
    }

    /**
     * Stub: resolves the gateway config and returns what a checkout
     * would need. No real charge is attempted here -- the concrete
     * gateway driver (Stripe/PayPal/...) is wired in when its SDK
     * and credentials are configured.
     */
    public static function createPayment(Order $order, string $gateway): array
    {
        if (! in_array(strtolower($gateway), static::GATEWAYS, true)) {
            return ['status' => 'error', 'message' => 'Unsupported payment gateway.'];
        }

        if (! static::isEnabled($gateway)) {
            return ['status' => 'not_configured', 'message' => 'Payment gateway is not enabled.'];
        }

        return [
            'status' => 'pending',
            'gateway' => $gateway,
            'order_id' => $order->id,
            'amount' => $order->payable_with_charge ?? $order->payable_amount,
            'currency' => $order->payable_currency,
            // 'redirect_url' / 'transaction_id' are filled in by the gateway driver.
        ];
    }
}
