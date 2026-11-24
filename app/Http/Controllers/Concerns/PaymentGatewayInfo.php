<?php

namespace App\Http\Controllers\Concerns;

use App\Services\PaymentService;

/**
 * Port of the old App\Traits\GetGlobalInformationTrait, backed by
 * PaymentService::gatewayConfig() (database payment_gateways /
 * basic_payments rows) instead of the old module models.
 * Charge percentages are read from the same gateway config keys
 * (e.g. stripe_charge, razorpay_charge).
 */
trait PaymentGatewayInfo
{
    public function get_basic_payment_info()
    {
        $info = [];
        foreach (['stripe', 'paypal', 'bank'] as $gateway) {
            foreach (PaymentService::gatewayConfig($gateway) as $key => $value) {
                $info[$key] = $value;
            }
        }

        return (object) $info;
    }

    public function get_payment_gateway_info()
    {
        $info = [];
        foreach (['razorpay', 'mollie', 'instamojo', 'flutterwave', 'paystack', 'uddoktapay'] as $gateway) {
            foreach (PaymentService::gatewayConfig($gateway) as $key => $value) {
                $info[$key] = $value;
            }
        }

        return (object) $info;
    }

    /** Port of the old calculate_payable_charge(): amount + gateway % charge, converted to the session currency. */
    protected function calculate_payable_charge($payable_amount, $gateway_name)
    {
        $gateway_charge = 0.0;
        $currency_rate = 1.0;
        $currency_code = getSessionCurrency() ?: 'USD';
        $country_code = 'US';
        $currency_id = 1;

        $basic = $this->get_basic_payment_info();
        $setting = $this->get_payment_gateway_info();
        $chargeKey = $gateway_name.'_charge';
        if (isset($basic->$chargeKey)) {
            $gateway_charge = (float) $basic->$chargeKey;
        } elseif (isset($setting->$chargeKey)) {
            $gateway_charge = (float) $setting->$chargeKey;
        }

        if (in_array($gateway_name, ['stripe', 'paypal', 'razorpay', 'mollie', 'instamojo', 'paystack', 'flutterwave', 'uddoktapay'], true)) {
            $gateway_currency = allCurrencies()->where('currency_code', getSessionCurrency())->first();
            if ($gateway_currency) {
                $currency_code = $gateway_currency->currency_code;
                $country_code = $gateway_currency->country_code ?? $country_code;
                $currency_rate = (float) ($gateway_currency->currency_rate ?: 1);
                $currency_id = $gateway_currency->id;
            }
        }

        session()->put('gateway_charge_in_usd', $payable_amount * ($gateway_charge / 100));

        $payable_amount = $payable_amount * $currency_rate;
        $gateway_charge_amount = $payable_amount * ($gateway_charge / 100);
        $payable_with_charge = sprintf('%0.2f', $payable_amount + $gateway_charge_amount);

        session()->put('gateway_charge', $gateway_charge_amount);
        session()->put('payable_currency', $currency_code);

        return (object) [
            'country_code' => $country_code,
            'currency_code' => $currency_code,
            'currency_id' => $currency_id,
            'gateway_charge' => $gateway_charge_amount,
            'payable_with_charge' => $payable_with_charge,
            'payable_amount' => $payable_amount,
        ];
    }
}
