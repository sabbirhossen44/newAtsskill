<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * bKash Tokenized Checkout flow moved out of BkashTokenizePaymentController.
 * Plain HTTP calls against the bKash API, using only config from
 * PaymentService::gatewayConfig('bkash') (bkash_app_key, bkash_app_secret,
 * bkash_username, bkash_password, bkash_base_url, bkash_callback_url from
 * the payment_gateways / basic_payments tables). No SDK keys are hard-coded.
 * Order creation, enrolment and instructor wallet credit on callback follow
 * the old callback logic.
 */
class BkashService
{
    public static function config(): array
    {
        $config = PaymentService::gatewayConfig('bkash');

        return [
            'base_url' => $config['bkash_base_url'] ?? $config['bkash_api_url'] ?? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta',
            'app_key' => $config['bkash_app_key'] ?? null,
            'app_secret' => $config['bkash_app_secret'] ?? null,
            'username' => $config['bkash_username'] ?? null,
            'password' => $config['bkash_password'] ?? null,
            'callback_url' => $config['bkash_callback_url'] ?? route('bkash-callBack'),
        ];
    }

    public static function token(array $config): ?array
    {
        if (! $config['app_key'] || ! $config['app_secret'] || ! $config['username'] || ! $config['password']) {
            return null;
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'username' => $config['username'],
            'password' => $config['password'],
        ])->post($config['base_url'].'/tokenized/checkout/token/grant', [
            'app_key' => $config['app_key'],
            'app_secret' => $config['app_secret'],
        ]);

        return $response->successful() ? $response->json() : null;
    }

    public static function headers(array $config, string $idToken): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $idToken,
            'X-APP-Key' => $config['app_key'],
        ];
    }

    /**
     * Start a bKash payment. Returns ['url' => bkashURL] on success or
     * ['error' => message] when bKash is not configured / the create call fails.
     */
    public static function createPayment($amount, array $cartItems): array
    {
        Session::put('payable_amount', $amount);
        Session::put('cart_items', $cartItems);
        Session::put('coupon_code', Session::get('coupon_code'));
        Session::put('offer_percentage', Session::get('offer_percentage'));
        Session::put('coupon_discount_amount', Session::get('coupon_discount_amount'));

        $config = static::config();
        $token = static::token($config);

        if (! $token || empty($token['id_token'])) {
            return ['error' => 'bKash is not configured'];
        }

        $inv = uniqid();
        $response = Http::withHeaders(static::headers($config, $token['id_token']))
            ->post($config['base_url'].'/tokenized/checkout/create', [
                'mode' => '0011',
                'payerReference' => $inv,
                'callbackURL' => $config['callback_url'],
                'amount' => (string) $amount,
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $inv,
            ]);

        $data = $response->json();

        if (isset($data['bkashURL'])) {
            return ['url' => $data['bkashURL']];
        }

        return ['error' => $data['statusMessage'] ?? 'Payment failed'];
    }

    /**
     * Handle the bKash callback. Returns ['outcome' => 'success'|'fail'|'cancel',
     * 'message' => string] — the controller maps it to the order-success /
     * order-fail redirects with the same flashes as before.
     */
    public static function handleCallback(?string $status, ?string $paymentID, ?User $user): array
    {
        if ($status == 'success') {
            $config = static::config();
            $token = static::token($config);

            if (! $token || empty($token['id_token'])) {
                return ['outcome' => 'fail', 'message' => 'Payment Failed'];
            }

            $response = Http::withHeaders(static::headers($config, $token['id_token']))
                ->post($config['base_url'].'/tokenized/checkout/execute', [
                    'paymentID' => $paymentID,
                ])->json();

            if (isset($response['statusCode']) && $response['statusCode'] == '0000') {
                $cellNumber = ! empty($response['customerMsisdn']) ? $response['customerMsisdn'] : ($user->phone ?? 'N/A');
                $bankDetails = json_encode([
                    'trxID' => $response['trxID'] ?? '',
                    'paymentID' => $response['paymentID'] ?? '',
                    'payerReference' => $response['payerReference'] ?? '',
                    'customerMsisdn' => $cellNumber,
                ]);

                $payable_amount = Session::get('payable_amount');
                $cart_items = Session::get('cart_items', []);

                OrderRepository::createFromCart([
                    'invoice_id' => Str::random(10),
                    'buyer_id' => $user->id,
                    'status' => 'completed',
                    'has_coupon' => Session::has('coupon_code') ? 1 : 0,
                    'coupon_code' => Session::get('coupon_code'),
                    'coupon_discount_percent' => Session::get('offer_percentage'),
                    'coupon_discount_amount' => Session::get('coupon_discount_amount'),
                    'payment_method' => 'Bkash',
                    'payment_status' => 'paid',
                    'payable_amount' => $payable_amount,
                    'gateway_charge' => 0,
                    'payable_with_charge' => $payable_amount,
                    'paid_amount' => $payable_amount,
                    'conversion_rate' => 1,
                    'payable_currency' => 'BDT',
                    'payment_details' => $bankDetails,
                    'transaction_id' => $response['trxID'] ?? Str::random(10),
                    'commission_rate' => setting('commission_rate'),
                ], CheckoutService::orderItemsFromCart(array_map(fn ($item) => (array) $item, $cart_items)), $user->id, true);

                CheckoutService::forgetPaymentSession(['cart_items']);

                return ['outcome' => 'success', 'message' => 'Payment Successful!'];
            }

            return ['outcome' => 'fail', 'message' => 'Payment Failed'];
        }

        if ($status == 'cancel') {
            return ['outcome' => 'fail', 'message' => 'Payment Cancelled'];
        }

        return ['outcome' => 'fail', 'message' => 'Transaction Failed'];
    }

    /** Search a transaction by trxID. Null when bKash is not configured. */
    public static function searchTransaction(string $trxID): ?array
    {
        $config = static::config();
        $token = static::token($config);

        if (! $token || empty($token['id_token'])) {
            return null;
        }

        return Http::withHeaders(static::headers($config, $token['id_token']))
            ->post($config['base_url'].'/tokenized/checkout/general/searchTransaction', [
                'trxID' => $trxID,
            ])->json();
    }

    /** Refund a payment. Null when bKash is not configured. */
    public static function refund(array $data): ?array
    {
        $config = static::config();
        $token = static::token($config);

        if (! $token || empty($token['id_token'])) {
            return null;
        }

        return Http::withHeaders(static::headers($config, $token['id_token']))
            ->post($config['base_url'].'/tokenized/checkout/payment/refund', [
                'paymentID' => $data['paymentID'],
                'trxID' => $data['trxID'],
                'amount' => (string) $data['amount'],
                'reason' => $data['reason'] ?? 'Refund',
                'sku' => 'refund',
            ])->json();
    }

    /** Refund status lookup by trxID. Null when bKash is not configured. */
    public static function refundStatus(string $trxID): ?array
    {
        return static::searchTransaction($trxID);
    }
}
