<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MultiCurrency;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

/**
 * Add-on payment gateways (Razorpay / Flutterwave / Paystack / Mollie /
 * Instamojo / UddoktaPay). Key/value rows in `payment_gateways`. The
 * rebuilt page only posts `status` + `public_key` per gateway, so each
 * update saves the keys present instead of the old full credential
 * sets; secrets are never rendered back. Mollie has no Laravel 13
 * package in this rebuild (config only), as noted in MEMORY.md.
 */
class PaymentGatewayController extends Controller
{
    public function paymentgateway()
    {
        $values = PaymentGateway::query()->pluck('value', 'key')->all();
        $currencies = MultiCurrency::get();
        $payment_setting = (object) $values;

        $gateways = [];
        foreach (['razorpay', 'flutterwave', 'paystack', 'mollie', 'instamojo', 'uddoktapay'] as $key) {
            $gateways[$key] = (object) [
                'status' => $values[$key.'_status'] ?? 'inactive',
                'public_key' => $values[$key.'_public_key'] ?? $values[$key.'_key'] ?? '',
            ];
        }

        return view('admin.payment-gateway.index', compact('payment_setting', 'currencies', 'gateways'));
    }

    protected function saveGateway(Request $request, string $gateway, array $keys): void
    {
        foreach ($keys as $key) {
            if ($request->has($key) && $request->input($key) !== null) {
                PaymentGateway::updateOrCreate(['key' => $key], ['value' => (string) $request->input($key)]);
            }
        }
        if ($request->filled('status')) {
            PaymentGateway::updateOrCreate(['key' => $gateway.'_status'], ['value' => (string) $request->input('status')]);
        }
        if ($request->filled('public_key')) {
            PaymentGateway::updateOrCreate(['key' => $gateway.'_public_key'], ['value' => (string) $request->input('public_key')]);
        }
    }

    protected function done()
    {
        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function razorpay_update(Request $request)
    {
        $this->saveGateway($request, 'razorpay', ['razorpay_key', 'razorpay_secret', 'razorpay_charge', 'razorpay_status', 'razorpay_name', 'razorpay_description', 'razorpay_theme_color']);

        return $this->done();
    }

    public function flutterwave_update(Request $request)
    {
        $this->saveGateway($request, 'flutterwave', ['flutterwave_public_key', 'flutterwave_secret_key', 'flutterwave_charge', 'flutterwave_app_name', 'flutterwave_status']);

        return $this->done();
    }

    public function paystack_update(Request $request)
    {
        $this->saveGateway($request, 'paystack', ['paystack_public_key', 'paystack_secret_key', 'paystack_charge', 'paystack_status']);

        return $this->done();
    }

    public function mollie_update(Request $request)
    {
        $this->saveGateway($request, 'mollie', ['mollie_api_key', 'mollie_charge', 'mollie_status']);

        return $this->done();
    }

    public function instamojo_update(Request $request)
    {
        $this->saveGateway($request, 'instamojo', ['instamojo_api_key', 'instamojo_auth_token', 'instamojo_charge', 'instamojo_status']);

        return $this->done();
    }

    public function uddoktapay_update(Request $request)
    {
        $this->saveGateway($request, 'uddoktapay', ['uddoktapay_api_key', 'uddoktapay_charge', 'uddoktapay_status']);

        return $this->done();
    }
}
