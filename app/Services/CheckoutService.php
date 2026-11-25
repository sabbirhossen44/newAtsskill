<?php

namespace App\Services;

use App\Http\Controllers\Concerns\FormatsCurrency;
use App\Models\Order;
use App\Models\User;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Checkout + payment-success/fail flow moved out of CheckOutController
 * and PaymentController: gateway credential objects for the checkout view,
 * order creation from the cart (order → order items → enrolments →
 * instructor wallet credit), and the payment session lifecycle.
 */
class CheckoutService
{
    use FormatsCurrency;

    protected CartService $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    /** Data for frontend.pages.checkout.index from the checkout page. */
    public function checkoutData(): array
    {
        $summary = $this->cart->summary();
        $payable_amount = $summary['cartTotal'] - $summary['discountAmount'];
        Session::put('payable_amount', $payable_amount);

        $basic_payment = PaymentService::basicPaymentInfo();
        $payment_setting = PaymentService::paymentGatewayInfo();

        $razorpay_charge = PaymentService::calculatePayableCharge($payable_amount, 'razorpay');
        $flutterwave_charge = PaymentService::calculatePayableCharge($payable_amount, 'flutterwave');
        $paystack_charge = PaymentService::calculatePayableCharge($payable_amount, 'paystack');

        return array_merge($summary, [
            'payable_amount' => $payable_amount,
            'basic_payment' => $basic_payment,
            'payment_setting' => $payment_setting,
            'user' => auth('web')->user(),
            'strip_key' => $basic_payment->stripe_key ?? null,
            'razorpay_credentials' => (object) [
                'currency_code' => $razorpay_charge->currency_code,
                'payable_with_charge' => $razorpay_charge->payable_with_charge,
                'razorpay_key' => $payment_setting->razorpay_key,
                'razorpay_secret' => $payment_setting->razorpay_secret,
                'razorpay_name' => $payment_setting->razorpay_name,
                'razorpay_description' => $payment_setting->razorpay_description,
                'razorpay_image' => $payment_setting->razorpay_image,
                'razorpay_theme_color' => $payment_setting->razorpay_theme_color,
                'razorpay_status' => $payment_setting->razorpay_status,
            ],
            'mollie_credentials' => (object) [
                'mollie_status' => $payment_setting->mollie_status,
                'mollie_image' => $payment_setting->mollie_image,
            ],
            'instamojo_credentials' => (object) [
                'instamojo_status' => $payment_setting->instamojo_status,
                'instamojo_image' => $payment_setting->instamojo_image,
            ],
            'flutterwave_credentials' => (object) [
                'country_code' => $flutterwave_charge->country_code,
                'currency_code' => $flutterwave_charge->currency_code,
                'payable_with_charge' => $flutterwave_charge->payable_with_charge,
                'flutterwave_public_key' => $payment_setting->flutterwave_public_key,
                'flutterwave_secret_key' => $payment_setting->flutterwave_secret_key,
                'flutterwave_app_name' => $payment_setting->flutterwave_app_name,
                'flutterwave_status' => $payment_setting->flutterwave_status,
                'flutterwave_image' => $payment_setting->flutterwave_image,
            ],
            'paystack_credentials' => (object) [
                'country_code' => $paystack_charge->country_code,
                'currency_code' => $paystack_charge->currency_code,
                'payable_with_charge' => $paystack_charge->payable_with_charge,
                'paystack_public_key' => $payment_setting->paystack_public_key,
                'paystack_secret_key' => $payment_setting->paystack_secret_key,
                'paystack_status' => $payment_setting->paystack_status,
                'paystack_image' => $payment_setting->paystack_image,
            ],
        ]);
    }

    /** Data for frontend.pages.checkout.index from the payment page (no totals/coupon/strip keys). */
    public function paymentData(): array
    {
        $payable_amount = Session::get('payable_amount', 0);
        $basic_payment = PaymentService::basicPaymentInfo();
        $payment_setting = PaymentService::paymentGatewayInfo();

        $razorpay_charge = PaymentService::calculatePayableCharge($payable_amount, 'razorpay');
        $flutterwave_charge = PaymentService::calculatePayableCharge($payable_amount, 'flutterwave');
        $paystack_charge = PaymentService::calculatePayableCharge($payable_amount, 'paystack');

        return [
            'user' => auth('web')->user(),
            'products' => $this->cart->content(),
            'payable_amount' => $payable_amount,
            'basic_payment' => $basic_payment,
            'payment_setting' => $payment_setting,
            'razorpay_credentials' => (object) [
                'currency_code' => $razorpay_charge->currency_code,
                'payable_with_charge' => $razorpay_charge->payable_with_charge,
                'razorpay_key' => $payment_setting->razorpay_key ?? null,
                'razorpay_secret' => $payment_setting->razorpay_secret ?? null,
                'razorpay_name' => $payment_setting->razorpay_name ?? null,
                'razorpay_description' => $payment_setting->razorpay_description ?? null,
                'razorpay_image' => $payment_setting->razorpay_image ?? null,
                'razorpay_theme_color' => $payment_setting->razorpay_theme_color ?? null,
                'razorpay_status' => $payment_setting->razorpay_status ?? null,
            ],
            'mollie_credentials' => (object) ['mollie_status' => $payment_setting->mollie_status ?? null],
            'instamojo_credentials' => (object) ['instamojo_status' => $payment_setting->instamojo_status ?? null],
            'flutterwave_credentials' => (object) [
                'country_code' => $flutterwave_charge->country_code,
                'currency_code' => $flutterwave_charge->currency_code,
                'payable_with_charge' => $flutterwave_charge->payable_with_charge,
                'flutterwave_public_key' => $payment_setting->flutterwave_public_key ?? null,
                'flutterwave_secret_key' => $payment_setting->flutterwave_secret_key ?? null,
                'flutterwave_app_name' => $payment_setting->flutterwave_app_name ?? null,
                'flutterwave_status' => $payment_setting->flutterwave_status ?? null,
                'flutterwave_image' => $payment_setting->flutterwave_image ?? null,
            ],
            'paystack_credentials' => (object) [
                'country_code' => $paystack_charge->country_code,
                'currency_code' => $paystack_charge->currency_code,
                'payable_with_charge' => $paystack_charge->payable_with_charge,
                'paystack_public_key' => $payment_setting->paystack_public_key ?? null,
                'paystack_secret_key' => $payment_setting->paystack_secret_key ?? null,
                'paystack_status' => $payment_setting->paystack_status ?? null,
            ],
        ];
    }

    /** True when any cart course id is already in the session enrolments list. */
    public function hasAlreadyEnrolledProduct(): bool
    {
        $enrollments = (array) session()->get('enrollments');

        foreach ($this->cart->content() as $product) {
            if (in_array($product->id, $enrollments)) {
                return true;
            }
        }

        return false;
    }

    /** Cart rows as plain order-item arrays for OrderRepository::createFromCart(). */
    public static function orderItemsFromCart(iterable $cartItems): array
    {
        $items = [];
        foreach ($cartItems as $item) {
            $items[] = [
                'price' => $item->price ?? $item['price'],
                'course_id' => $item->id ?? $item['course_id'],
            ];
        }

        return $items;
    }

    /** Bank payment: order with payment_status pending, enrolments, no wallet credit. */
    public function createBankOrder(User $user, string $bankDetails): Order
    {
        $payable_amount = Session::get('payable_amount');
        $charge = PaymentService::calculatePayableCharge($payable_amount, getSessionCurrency());

        return OrderRepository::createFromCart([
            'invoice_id' => Str::random(10),
            'buyer_id' => $user->id,
            'status' => 'completed',
            'has_coupon' => Session::has('coupon_code') ? 1 : 0,
            'coupon_code' => Session::get('coupon_code'),
            'coupon_discount_percent' => Session::get('offer_percentage'),
            'coupon_discount_amount' => Session::get('coupon_discount_amount'),
            'payment_method' => 'Bank',
            'payment_status' => 'pending',
            'payable_amount' => $payable_amount,
            'gateway_charge' => 0,
            'payable_with_charge' => $charge->payable_with_charge,
            'paid_amount' => $charge->payable_with_charge,
            'conversion_rate' => 1,
            'payable_currency' => getSessionCurrency(),
            'payment_details' => $bankDetails,
            'transaction_id' => Str::random(10),
            'commission_rate' => setting('commission_rate'),
        ], static::orderItemsFromCart($this->cart->content()), $user->id);
    }

    /**
     * Gateway / free payment success: paid order from the pending-payment
     * session state, enrolments, instructor wallet credit, GTM dataLayer
     * payload and the order-completed mail.
     */
    public function createPaidOrderFromSession(User $user): Order
    {
        $order = OrderRepository::createFromCart([
            'invoice_id' => Str::random(10),
            'buyer_id' => $user->id,
            'status' => 'completed',
            'has_coupon' => Session::has('coupon_code') ? 1 : 0,
            'coupon_code' => Session::get('coupon_code'),
            'coupon_discount_percent' => Session::get('offer_percentage'),
            'coupon_discount_amount' => Session::get('coupon_discount_amount'),
            'payment_method' => Session::get('after_success_gateway'),
            'payment_status' => 'paid',
            'payable_amount' => Session::get('payable_amount'),
            'gateway_charge' => Session::get('gateway_charge_in_usd'),
            'payable_with_charge' => Session::get('payable_with_charge', 0),
            'paid_amount' => Session::get('paid_amount', 0),
            'payable_currency' => Session::get('payable_currency'),
            'conversion_rate' => Session::get('currency_rate', 1),
            'payment_details' => json_encode(Session::get('payment_details')),
            'transaction_id' => Session::get('after_success_transaction'),
            'commission_rate' => setting('commission_rate'),
        ], static::orderItemsFromCart($this->cart->content()), $user->id, true);

        $data_layer_order_items = [];
        foreach ($this->cart->content() as $item) {
            $data_layer_order_items[] = [
                'course_name' => $item->name,
                'price' => $this->formatCurrency($item->price),
                'url' => route('course.show', $item->options->slug),
            ];
        }

        if ($user && setting('google_tagmanager_status') === 'active') {
            session()->put('enrollSuccess', [
                'invoice_id' => $order->invoice_id,
                'transaction_id' => $order->transaction_id,
                'payment_method' => $order->payment_method,
                'payable_currency' => $order->payable_currency,
                'paid_amount' => $order->paid_amount,
                'payment_status' => $order->payment_status,
                'order_items' => $data_layer_order_items,
                'student_info' => ['name' => $user->name, 'email' => $user->email],
            ]);
        }

        try {
            PaymentService::sendOrderCompletedMail([
                'email' => $user->email,
                'name' => $user->name,
                'order_id' => $order->invoice_id,
                'paid_amount' => $order->paid_amount.' '.$order->payable_currency,
                'payment_method' => $order->payment_method,
            ]);
        } catch (\Exception $e) {
            \Log::error('Mail sending error in payment_addon_success: '.$e->getMessage());
        }

        return $order;
    }

    /** Record a failed payment attempt in the GTM dataLayer session payload. */
    public function recordFailedPayment(?User $user): void
    {
        $data_layer_order_items = [];
        foreach ($this->cart->content() as $item) {
            $data_layer_order_items[] = [
                'course_name' => $item->name,
                'price' => $this->formatCurrency($item->price),
                'url' => route('course.show', $item->options->slug),
            ];
        }

        if (setting('google_tagmanager_status') === 'active') {
            session()->put('enrollFailed', [
                'payable_currency' => session('payable_currency', getSessionCurrency()),
                'paid_amount' => session('paid_amount', null),
                'payment_status' => 'Failed',
                'order_items' => $data_layer_order_items,
                'student_info' => ['name' => $user->name, 'email' => $user->email],
            ]);
        }
    }

    public static function forgetPaymentSession(array $extra = []): void
    {
        Session::forget(array_merge([
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
            'coupon_code',
            'offer_percentage',
            'coupon_discount_amount',
            'gateway_charge_in_usd',
        ], $extra));
    }
}
