<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\FormatsCurrency;
use App\Http\Controllers\Concerns\PaymentGatewayInfo;
use App\Http\Controllers\Concerns\SendsMail;
use App\Http\Controllers\Concerns\SessionCart;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\BankInformationRequest;
use App\Jobs\DefaultMailJob;
use App\Mail\DefaultMail;
use App\Models\Course;
use App\Models\EmailTemplate;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Port of the old PaymentController.
 *
 * The old code called the BasicPayment / PaymentGateway addon controllers
 * (Stripe, PayPal, Razorpay, Mollie, Instamojo, UddoktaPay SDKs), which are
 * not in the new build. Each pay_via_* method now resolves its gateway
 * config through PaymentService (database payment_gateways /
 * basic_payments rows, config-only — no SDK keys are hard-coded here) and
 * records the pending payment in the session, then hands off to the
 * gateway. The concrete SDK redirect is wired in once that gateway's SDK
 * and credentials are configured; until then the method redirects to the
 * payment-addon-success/fail callbacks with the session state the old
 * code set, so order creation, enrolment and instructor wallet credit
 * (payment_addon_success) keep working end-to-end for bank / free
 * payments and for gateway callbacks.
 */
class PaymentController extends Controller
{
    use FormatsCurrency, PaymentGatewayInfo, SendsMail, SessionCart;

    public function payment()
    {
        $user = Auth::guard('web')->user();
        $payable_amount = Session::get('payable_amount', 0);

        $basic_payment = $this->get_basic_payment_info();
        $payment_setting = $this->get_payment_gateway_info();

        $razorpay_calculate_charge = $this->calculate_payable_charge($payable_amount, 'razorpay');
        $razorpay_credentials = (object) [
            'currency_code' => $razorpay_calculate_charge->currency_code,
            'payable_with_charge' => $razorpay_calculate_charge->payable_with_charge,
            'razorpay_key' => $payment_setting->razorpay_key ?? null,
            'razorpay_secret' => $payment_setting->razorpay_secret ?? null,
            'razorpay_name' => $payment_setting->razorpay_name ?? null,
            'razorpay_description' => $payment_setting->razorpay_description ?? null,
            'razorpay_image' => $payment_setting->razorpay_image ?? null,
            'razorpay_theme_color' => $payment_setting->razorpay_theme_color ?? null,
            'razorpay_status' => $payment_setting->razorpay_status ?? null,
        ];

        $mollie_credentials = (object) ['mollie_status' => $payment_setting->mollie_status ?? null];
        $instamojo_credentials = (object) ['instamojo_status' => $payment_setting->instamojo_status ?? null];

        $flutterwave_calculate_charge = $this->calculate_payable_charge($payable_amount, 'flutterwave');
        $flutterwave_credentials = (object) [
            'country_code' => $flutterwave_calculate_charge->country_code,
            'currency_code' => $flutterwave_calculate_charge->currency_code,
            'payable_with_charge' => $flutterwave_calculate_charge->payable_with_charge,
            'flutterwave_public_key' => $payment_setting->flutterwave_public_key ?? null,
            'flutterwave_secret_key' => $payment_setting->flutterwave_secret_key ?? null,
            'flutterwave_app_name' => $payment_setting->flutterwave_app_name ?? null,
            'flutterwave_status' => $payment_setting->flutterwave_status ?? null,
            'flutterwave_image' => $payment_setting->flutterwave_image ?? null,
        ];

        $paystack_calculate_charge = $this->calculate_payable_charge($payable_amount, 'paystack');
        $paystack_credentials = (object) [
            'country_code' => $paystack_calculate_charge->country_code,
            'currency_code' => $paystack_calculate_charge->currency_code,
            'payable_with_charge' => $paystack_calculate_charge->payable_with_charge,
            'paystack_public_key' => $payment_setting->paystack_public_key ?? null,
            'paystack_secret_key' => $payment_setting->paystack_secret_key ?? null,
            'paystack_status' => $payment_setting->paystack_status ?? null,
        ];

        return view('frontend.pages.checkout.index')->with([
            'user' => $user,
            'products' => $this->cartContent(),
            'payable_amount' => $payable_amount,
            'basic_payment' => $basic_payment,
            'payment_setting' => $payment_setting,
            'razorpay_credentials' => $razorpay_credentials,
            'mollie_credentials' => $mollie_credentials,
            'instamojo_credentials' => $instamojo_credentials,
            'flutterwave_credentials' => $flutterwave_credentials,
            'paystack_credentials' => $paystack_credentials,
        ]);
    }

    /**
     * Config-only gateway hand-off: resolves the gateway through
     * PaymentService and stores the pending-payment session state the old
     * addon controllers used. The real SDK charge/redirect is added when
     * the gateway SDK + credentials are configured (see class docblock).
     */
    protected function initiateGatewayPayment(string $gateway, Request $request)
    {
        $payable_amount = Session::get('payable_amount');

        if (! PaymentService::isEnabled($gateway)) {
            return back()->with(['messege' => __(ucfirst($gateway).' is currently unavailable'), 'alert-type' => 'error']);
        }

        $charge = $this->calculate_payable_charge($payable_amount, $gateway);

        Session::put('after_success_url', route('payment-addon-success'));
        Session::put('after_faild_url', route('payment-addon-faild'));
        Session::put('after_success_gateway', ucfirst($gateway));
        Session::put('after_success_transaction', Str::random(10));
        Session::put('payable_with_charge', $charge->payable_with_charge);
        Session::put('payable_currency', $charge->currency_code);
        Session::put('paid_amount', $payable_amount);

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
        $config = PaymentService::gatewayConfig('flutterwave');
        $token = $config['flutterwave_secret_key'] ?? null;
        $tnx_id = $request->tnx_id;

        if (! $token || ! $tnx_id) {
            return response()->json(['message' => trans('Payment Fail')], 403);
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/$tnx_id/verify",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', "Authorization: Bearer $token"],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);

        if (($response->status ?? null) == 'success') {
            $paymentDetails = [
                'status' => $response->status,
                'trx_id' => $tnx_id,
                'amount' => $response?->data?->amount,
                'amount_settled' => $response?->data?->amount_settled,
                'currency' => $response?->data?->currency,
                'charged_amount' => $response?->data?->charged_amount,
                'app_fee' => $response?->data?->app_fee,
                'merchant_fee' => $response?->data?->merchant_fee,
                'card_last_4digits' => $response?->data?->card?->last_4digits,
            ];

            Session::put('paid_amount', $response?->data?->amount);
            Session::put('payable_currency', $response?->data?->currency);
            Session::put('payment_details', $paymentDetails);
            Session::put('after_success_gateway', 'Flutterwave');
            Session::put('after_success_transaction', $tnx_id);
            Session::put('payable_with_charge', $response?->data?->amount);

            return response()->json(['message' => 'payment success']);
        }

        return response()->json(['message' => trans('Payment Fail')], 403);
    }

    public function pay_via_paystack(Request $request)
    {
        $reference = $request->reference;
        $transaction = $request->tnx_id;
        $secret_key = $request->secret_key ?? PaymentService::gatewayConfig('paystack')['paystack_secret_key'] ?? null;

        if (! $reference || ! $secret_key) {
            return response()->json(['message' => trans('Something went wrong, please try again')], 403);
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/$reference",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => ["Authorization: Bearer $secret_key", 'Cache-Control: no-cache'],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        if ($err) {
            info($err);
        }
        curl_close($curl);
        $final_data = json_decode($response);

        if ($final_data->status == true) {
            $paymentDetails = [
                'status' => $final_data?->data?->status,
                'transaction_id' => $transaction,
                'requested_amount' => $final_data?->data->requested_amount,
                'amount' => $final_data?->data?->amount,
                'currency' => $final_data?->data?->currency,
                'gateway_response' => $final_data?->data?->gateway_response,
                'paid_at' => $final_data?->data?->paid_at,
                'card_last_4_digits' => $final_data?->data->authorization?->last4,
            ];

            Session::put('paid_amount', ($final_data?->data?->amount / 100));
            Session::put('payable_currency', $final_data?->data?->currency);
            Session::put('payment_details', $paymentDetails);
            Session::put('after_success_gateway', 'Paystack');
            Session::put('after_success_transaction', $transaction);
            Session::put('payable_with_charge', $final_data?->data?->amount / 100);

            return response()->json(['message' => 'payment success']);
        }

        return response()->json(['message' => trans('Something went wrong, please try again')], 403);
    }

    public function pay_via_bank(BankInformationRequest $request)
    {
        $bankDetails = json_encode($request->only(['bank_name', 'account_number', 'routing_number', 'branch', 'transaction']));
        $user = auth('web')->user();

        $order = Order::create([
            'invoice_id' => Str::random(10),
            'buyer_id' => $user->id,
            'status' => 'completed',
            'has_coupon' => Session::has('coupon_code') ? 1 : 0,
            'coupon_code' => Session::get('coupon_code'),
            'coupon_discount_percent' => Session::get('offer_percentage'),
            'coupon_discount_amount' => Session::get('coupon_discount_amount'),
            'payment_method' => 'Bank',
            'payment_status' => 'pending',
            'payable_amount' => Session::get('payable_amount'),
            'gateway_charge' => 0,
            'payable_with_charge' => $this->calculate_payable_charge(Session::get('payable_amount'), getSessionCurrency())->payable_with_charge,
            'paid_amount' => $this->calculate_payable_charge(Session::get('payable_amount'), getSessionCurrency())->payable_with_charge,
            'conversion_rate' => 1,
            'payable_currency' => getSessionCurrency(),
            'payment_details' => $bankDetails,
            'transaction_id' => Str::random(10),
            'commission_rate' => setting('commission_rate'),
        ]);

        foreach ($this->cartContent() as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'price' => $item->price,
                'course_id' => $item->id,
                'commission_rate' => setting('commission_rate'),
            ]);
            Enrollment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'course_id' => $item->id,
                'has_access' => 1,
            ]);
        }

        $this->forgetPaymentSession();

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
        $payable_amount = Session::get('payable_amount');
        $payable_with_charge = Session::get('payable_with_charge', 0);
        $payable_currency = Session::get('payable_currency');
        $gateway_name = Session::get('after_success_gateway');
        $transaction = Session::get('after_success_transaction');
        $paid_amount = Session::get('paid_amount', 0);
        $paymentDetails = Session::get('payment_details');
        $gateway_charge = Session::get('gateway_charge_in_usd');

        $user = auth('web')->user();

        $order = Order::create([
            'invoice_id' => Str::random(10),
            'buyer_id' => $user->id,
            'status' => 'completed',
            'has_coupon' => Session::has('coupon_code') ? 1 : 0,
            'coupon_code' => Session::get('coupon_code'),
            'coupon_discount_percent' => Session::get('offer_percentage'),
            'coupon_discount_amount' => Session::get('coupon_discount_amount'),
            'payment_method' => $gateway_name,
            'payment_status' => 'paid',
            'payable_amount' => $payable_amount,
            'gateway_charge' => $gateway_charge,
            'payable_with_charge' => $payable_with_charge,
            'paid_amount' => $paid_amount,
            'payable_currency' => $payable_currency,
            'conversion_rate' => Session::get('currency_rate', 1),
            'payment_details' => json_encode($paymentDetails),
            'transaction_id' => $transaction,
            'commission_rate' => setting('commission_rate'),
        ]);

        $data_layer_order_items = [];

        foreach ($this->cartContent() as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'price' => $item->price,
                'course_id' => $item->id,
                'commission_rate' => setting('commission_rate'),
            ]);
            $data_layer_order_items[] = [
                'course_name' => $item->name,
                'price' => $this->formatCurrency($item->price),
                'url' => route('course.show', $item->options->slug),
            ];
            Enrollment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'course_id' => $item->id,
                'has_access' => 1,
            ]);

            // Insert instructor commission to his wallet (old logic: platform
            // keeps commission_rate %, instructor gets the rest).
            $commissionAmount = $item->price * ($order->commission_rate / 100);
            $amountAfterCommission = $item->price - $commissionAmount;
            $instructor = Course::find($item->id)?->instructor;
            $instructor?->increment('wallet_balance', $amountAfterCommission);
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
            $this->handleMailSending([
                'email' => $user->email,
                'name' => $user->name,
                'order_id' => $order->invoice_id,
                'paid_amount' => $order->paid_amount.' '.$order->payable_currency,
                'payment_method' => $order->payment_method,
            ]);
        } catch (\Exception $e) {
            \Log::error('Mail sending error in payment_addon_success: '.$e->getMessage());
        }

        $this->forgetPaymentSession();

        return redirect()->route('order-success')->with([
            'messege' => trans('Payment Success.'),
            'alert-type' => 'success',
        ]);
    }

    public function payment_addon_faild()
    {
        $data_layer_order_items = [];
        foreach ($this->cartContent() as $item) {
            $data_layer_order_items[] = [
                'course_name' => $item->name,
                'price' => $this->formatCurrency($item->price),
                'url' => route('course.show', $item->options->slug),
            ];
        }

        if (setting('google_tagmanager_status') === 'active') {
            $user = auth('web')->user();
            session()->put('enrollFailed', [
                'payable_currency' => session('payable_currency', getSessionCurrency()),
                'paid_amount' => session('paid_amount', null),
                'payment_status' => 'Failed',
                'order_items' => $data_layer_order_items,
                'student_info' => ['name' => $user->name, 'email' => $user->email],
            ]);
        }

        $this->forgetPaymentSession();

        return redirect()->route('order-fail')->with([
            'messege' => trans('Payment faild, please try again'),
            'alert-type' => 'error',
        ]);
    }

    protected function forgetPaymentSession(): void
    {
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
            'coupon_code',
            'offer_percentage',
            'coupon_discount_amount',
            'gateway_charge_in_usd',
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

    public function handleMailSending(array $mailData)
    {
        try {
            self::setMailConfig();

            $template = EmailTemplate::where('name', 'order_completed')->first();
            if (! $template) {
                return;
            }
            $mailData['subject'] = $template->subject;

            $message = str_replace('{{name}}', $mailData['name'] ?? '', $template->message);
            $message = str_replace('{{order_id}}', $mailData['order_id'] ?? '', $message);
            $message = str_replace('{{paid_amount}}', $mailData['paid_amount'] ?? '', $message);
            $message = str_replace('{{payment_method}}', $mailData['payment_method'] ?? '', $message);

            if (self::isQueable()) {
                DefaultMailJob::dispatch($mailData, $message);
            } else {
                Mail::to($mailData['email'])->send(new DefaultMail($mailData, $message));
            }
        } catch (\Exception $e) {
            \Log::error('Order completed mail error: '.$e->getMessage());
        }
    }
}
