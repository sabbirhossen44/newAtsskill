<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SessionCart;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Port of the old BkashTokenizePaymentController.
 *
 * The old code used the karim007/laravel-bkash-tokenize package
 * (BkashPaymentTokenize / BkashRefundTokenize facades) and a PppSecret
 * model, none of which exist in the new build. The bKash Tokenized
 * Checkout flow is implemented here with plain HTTP calls against the
 * bKash API, using only config from PaymentService::gatewayConfig('bkash')
 * (bkash_app_key, bkash_app_secret, bkash_username, bkash_password,
 * bkash_base_url, bkash_callback_url from the payment_gateways /
 * basic_payments tables). No SDK keys are hard-coded. Order creation,
 * enrolment and instructor wallet credit on callback follow the old
 * callback logic (plus wallet credit, matching payment_addon_success).
 */
class BkashTokenizePaymentController extends Controller
{
    use SessionCart;

    protected function bkashConfig(): array
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

    protected function bkashToken(array $config): ?array
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

    protected function bkashHeaders(array $config, string $idToken): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $idToken,
            'X-APP-Key' => $config['app_key'],
        ];
    }

    public function index()
    {
        return view('frontend.pages.checkout.index', [
            'products' => $this->cartContent(),
            'payable_amount' => Session::get('payable_amount', 0),
        ]);
    }

    public function createPayment(Request $request)
    {
        Session::put('payable_amount', $request->input('amount'));
        Session::put('cart_items', $this->cartContent()->toArray());
        Session::put('coupon_code', Session::get('coupon_code'));
        Session::put('offer_percentage', Session::get('offer_percentage'));
        Session::put('coupon_discount_amount', Session::get('coupon_discount_amount'));

        $config = $this->bkashConfig();
        $token = $this->bkashToken($config);

        if (! $token || empty($token['id_token'])) {
            return redirect()->back()->with('error-alert2', 'bKash is not configured');
        }

        $inv = uniqid();
        $response = Http::withHeaders($this->bkashHeaders($config, $token['id_token']))
            ->post($config['base_url'].'/tokenized/checkout/create', [
                'mode' => '0011',
                'payerReference' => $inv,
                'callbackURL' => $config['callback_url'],
                'amount' => (string) $request->input('amount'),
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $inv,
            ]);

        $data = $response->json();

        if (isset($data['bkashURL'])) {
            return redirect()->away($data['bkashURL']);
        }

        return redirect()->back()->with('error-alert2', $data['statusMessage'] ?? 'Payment failed');
    }

    public function callBack(Request $request)
    {
        if ($request->status == 'success') {
            $config = $this->bkashConfig();
            $token = $this->bkashToken($config);

            if (! $token || empty($token['id_token'])) {
                return redirect()->route('order-fail')->with('error', 'Payment Failed');
            }

            $response = Http::withHeaders($this->bkashHeaders($config, $token['id_token']))
                ->post($config['base_url'].'/tokenized/checkout/execute', [
                    'paymentID' => $request->paymentID,
                ])->json();

            if (isset($response['statusCode']) && $response['statusCode'] == '0000') {
                $user = auth('web')->user();
                $cellNumber = ! empty($response['customerMsisdn']) ? $response['customerMsisdn'] : ($user->phone ?? 'N/A');
                $bankDetails = json_encode([
                    'trxID' => $response['trxID'] ?? '',
                    'paymentID' => $response['paymentID'] ?? '',
                    'payerReference' => $response['payerReference'] ?? '',
                    'customerMsisdn' => $cellNumber,
                ]);

                $payable_amount = Session::get('payable_amount');
                $cart_items = Session::get('cart_items', []);

                $order = Order::create([
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
                ]);

                foreach ($cart_items as $item) {
                    $item = (array) $item;
                    OrderItem::create([
                        'order_id' => $order->id,
                        'price' => $item['price'],
                        'course_id' => $item['id'],
                        'commission_rate' => setting('commission_rate'),
                    ]);
                    Enrollment::create([
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'course_id' => $item['id'],
                        'has_access' => 1,
                    ]);

                    // Instructor wallet credit (same rule as payment_addon_success).
                    $commissionAmount = $item['price'] * ($order->commission_rate / 100);
                    $instructor = Course::find($item['id'])?->instructor;
                    $instructor?->increment('wallet_balance', $item['price'] - $commissionAmount);
                }

                Session::forget([
                    'after_success_url',
                    'after_faild_url',
                    'payable_amount',
                    'gateway_charge',
                    'after_success_gateway',
                    'after_success_transaction',
                    'subscription_plan_id',
                    'payable_with_charge',
                    'payable_currency',
                    'paid_amount',
                    'payment_details',
                    'cart',
                    'cart_items',
                    'coupon_code',
                    'offer_percentage',
                    'coupon_discount_amount',
                    'gateway_charge_in_usd',
                ]);

                return redirect()->route('order-success')->with('success', 'Payment Successful!');
            }

            return redirect()->route('order-fail')->with('error', 'Payment Failed');
        } elseif ($request->status == 'cancel') {
            return redirect()->route('order-fail')->with('error', 'Payment Cancelled');
        }

        return redirect()->route('order-fail')->with('error', 'Transaction Failed');
    }

    public function searchTnx($trxID)
    {
        $config = $this->bkashConfig();
        $token = $this->bkashToken($config);

        if (! $token || empty($token['id_token'])) {
            return response()->json(['message' => 'bKash is not configured'], 503);
        }

        return Http::withHeaders($this->bkashHeaders($config, $token['id_token']))
            ->post($config['base_url'].'/tokenized/checkout/general/searchTransaction', [
                'trxID' => $trxID,
            ])->json();
    }

    public function refund(Request $request)
    {
        $config = $this->bkashConfig();
        $token = $this->bkashToken($config);

        if (! $token || empty($token['id_token'])) {
            return response()->json(['message' => 'bKash is not configured'], 503);
        }

        $request->validate([
            'paymentID' => 'required|string',
            'trxID' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        return Http::withHeaders($this->bkashHeaders($config, $token['id_token']))
            ->post($config['base_url'].'/tokenized/checkout/payment/refund', [
                'paymentID' => $request->paymentID,
                'trxID' => $request->trxID,
                'amount' => (string) $request->amount,
                'reason' => $request->reason ?? 'Refund',
                'sku' => 'refund',
            ])->json();
    }

    public function refundStatus(Request $request)
    {
        $config = $this->bkashConfig();
        $token = $this->bkashToken($config);

        if (! $token || empty($token['id_token'])) {
            return response()->json(['message' => 'bKash is not configured'], 503);
        }

        $request->validate(['trxID' => 'required|string']);

        return Http::withHeaders($this->bkashHeaders($config, $token['id_token']))
            ->post($config['base_url'].'/tokenized/checkout/general/searchTransaction', [
                'trxID' => $request->trxID,
            ])->json();
    }
}
