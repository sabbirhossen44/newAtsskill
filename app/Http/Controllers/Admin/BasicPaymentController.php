<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BasicPayment;
use App\Models\MultiCurrency;
use Illuminate\Http\Request;

/**
 * Basic (Stripe / PayPal / bank) payment settings. Key/value rows in
 * `basic_payments`; the rebuilt form posts only the fields it renders,
 * so updates save the keys present instead of requiring the old full
 * credential sets (secrets are never rendered back).
 */
class BasicPaymentController extends Controller
{
    public function basicpayment()
    {
        $basicPayment = (object) BasicPayment::query()->pluck('value', 'key')->all();
        $currencies = MultiCurrency::get();

        return view('admin.basic-payment.index', compact('basicPayment', 'currencies'));
    }

    protected function saveKeys(Request $request, array $keys): void
    {
        foreach ($keys as $key) {
            if ($request->has($key) && $request->input($key) !== null) {
                BasicPayment::updateOrCreate(['key' => $key], ['value' => (string) $request->input($key)]);
            }
        }
        if ($request->hasFile('stripe_image')) {
            $row = BasicPayment::where('key', 'stripe_image')->first();
            $row?->update(['value' => \App\Services\UploadService::store($request->file('stripe_image'), 'uploads/custom-images/', $row->value)]);
        }
        if ($request->hasFile('paypal_image')) {
            $row = BasicPayment::where('key', 'paypal_image')->first();
            $row?->update(['value' => \App\Services\UploadService::store($request->file('paypal_image'), 'uploads/custom-images/', $row->value)]);
        }
    }

    public function update_stripe(Request $request)
    {
        $this->saveKeys($request, ['stripe_key', 'stripe_secret', 'stripe_charge', 'stripe_status', 'stripe_image']);

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function update_paypal(Request $request)
    {
        $this->saveKeys($request, ['paypal_client_id', 'paypal_secret_key', 'paypal_charge', 'paypal_status', 'paypal_account_mode', 'paypal_app_id', 'paypal_image']);

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function update_bank_payment(Request $request)
    {
        $this->saveKeys($request, ['bank_details', 'bank_status', 'bank_name', 'bank_account_name', 'bank_account_number', 'bank_routing_number']);

        return redirect()->back()->with('success', __('Update Successfully'));
    }
}
