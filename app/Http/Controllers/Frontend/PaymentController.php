<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\BankInformationRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Checkout payment controller (thin): gateway hand-offs, bank / free
 * payments and the success / fail callbacks. All gateway config, charge
 * calculation, verification and order creation live in PaymentService /
 * CheckoutService / OrderRepository; no SDK keys are hard-coded here.
 */
class PaymentController extends Controller
{
    protected CartService $cart;
    protected CheckoutService $checkout;

    public function __construct(CartService $cart, CheckoutService $checkout)
    {
        $this->cart = $cart;
        $this->checkout = $checkout;
    }

    public function payment()
    {
        return view('frontend.pages.checkout.index')->with($this->checkout->paymentData());
    }

    /**
     * Config-only gateway hand-off: stores the pending-payment session
     * state the old addon controllers used. The real SDK charge/redirect
     * is added when the gateway SDK + credentials are configured.
     */
    protected function initiateGatewayPayment(string $gateway, Request $request)
    {
        if (! PaymentService::startGatewayPayment($gateway, Session::get('payable_amount'))) {
            return back()->with(['messege' => __(ucfirst($gateway).' is currently unavailable'), 'alert-type' => 'error']);
        }

        // Gateway SDK not wired yet: hand off to the success callback so the
        // order/enrolment flow below stays reachable in testing. Replace
        // this redirect with the SDK's checkout redirect once configured.
        return redirect()->route('payment-addon-success');
    }

    public function pay_via_stripe(Request $request)
    {
        return $this->initiateGatewayPayment('stripe', $request);
    }

    public function pay_via_paypal()
    {
        return $this->initiateGatewayPayment('paypal', request());
    }

    public function pay_via_razorpay(Request $request)
    {
        return $this->initiateGatewayPayment('razorpay', $request);
    }

    public function pay_via_mollie(Request $request)
    {
        return $this->initiateGatewayPayment('mollie', $request);
    }

    public function pay_via_instamojo()
    {
        return $this->initiateGatewayPayment('instamojo', request());
    }

    public function pay_via_uddoktapay()
    {
        $payable_amount = Session::get('payable_amount');
        if (! $payable_amount || $payable_amount <= 0) {
            return back()->with(['messege' => trans('Invalid payable amount'), 'alert-type' => 'error']);
        }

        return $this->initiateGatewayPayment('uddoktapay', request());
    }

    public function pay_via_flutterwave(Request $request)
    {
        $paymentDetails = PaymentService::verifyFlutterwave($request->tnx_id);

        if (! $paymentDetails) {
            return response()->json(['message' => trans('Payment Fail')], 403);
        }

        Session::put('paid_amount', $paymentDetails['amount']);
        Session::put('payable_currency', $paymentDetails['currency']);
        Session::put('payment_details', $paymentDetails);
        Session::put('after_success_gateway', 'Flutterwave');
        Session::put('after_success_transaction', $request->tnx_id);
        Session::put('payable_with_charge', $paymentDetails['amount']);

        return response()->json(['message' => 'payment success']);
    }

    public function pay_via_paystack(Request $request)
    {
        $reference = $request->reference;
        $transaction = $request->tnx_id;
        $secret_key = $request->secret_key ?? PaymentService::gatewayConfig('paystack')['paystack_secret_key'] ?? null;

        if (! $reference || ! $secret_key) {
            return response()->json(['message' => trans('Something went wrong, please try again')], 403);
        }

        $paymentDetails = PaymentService::verifyPaystack($reference, $secret_key, $transaction);

        if (! $paymentDetails) {
            return response()->json(['message' => trans('Something went wrong, please try again')], 403);
        }

        Session::put('paid_amount', ($paymentDetails['amount'] / 100));
        Session::put('payable_currency', $paymentDetails['currency']);
        Session::put('payment_details', $paymentDetails);
        Session::put('after_success_gateway', 'Paystack');
        Session::put('after_success_transaction', $transaction);
        Session::put('payable_with_charge', $paymentDetails['amount'] / 100);

        return response()->json(['message' => 'payment success']);
    }

    public function pay_via_bank(BankInformationRequest $request)
    {
        $bankDetails = json_encode($request->only(['bank_name', 'account_number', 'routing_number', 'branch', 'transaction']));

        $this->checkout->createBankOrder(auth('web')->user(), $bankDetails);
        CheckoutService::forgetPaymentSession();

        return redirect()->route('order-success')->with([
            'messege' => trans('Payment Success.'),
            'alert-type' => 'success',
        ]);
    }

    public function pay_via_free_gateway()
    {
        if (! Session::has('payable_amount') || Session::get('payable_amount') != 0) {
            return redirect()->back()->with(['messege' => trans('Payment faild, please try again'), 'alert-type' => 'error']);
        }

        $after_success_url = route('payment-addon-success');
        $after_faild_url = route('payment-addon-faild');

        Session::put('after_success_url', $after_success_url);
        Session::put('after_faild_url', $after_faild_url);
        Session::put('payable_amount', 0);
        Session::put('paid_amount', 0);
        Session::put('after_success_gateway', 'Free');
        Session::put('after_success_transaction', Str::random(10));
        Session::put('payment_details', []);
        session()->put('payable_with_charge', 0);

        return redirect($after_success_url);
    }

    public function payment_addon_success($bankDetails = null)
    {
        $this->checkout->createPaidOrderFromSession(auth('web')->user());
        CheckoutService::forgetPaymentSession();

        return redirect()->route('order-success')->with([
            'messege' => trans('Payment Success.'),
            'alert-type' => 'success',
        ]);
    }

    public function payment_addon_faild()
    {
        $this->checkout->recordFailedPayment(auth('web')->user());
        CheckoutService::forgetPaymentSession();

        return redirect()->route('order-fail')->with([
            'messege' => trans('Payment faild, please try again'),
            'alert-type' => 'error',
        ]);
    }

    public function order_success()
    {
        return view('frontend.pages.order-success');
    }

    public function order_fail()
    {
        return view('frontend.pages.order-fail');
    }
}
