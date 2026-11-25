<?php

namespace App\Services;

use App\Http\Controllers\Concerns\SendsMail;
use App\Jobs\DefaultMailJob;
use App\Mail\DefaultMail;
use App\Models\BasicPayment;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class PaymentService
{
    use SendsMail;

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
     * Port of the old GetGlobalInformationTrait::get_basic_payment_info() /
     * get_payment_gateway_info() (previously in the PaymentGatewayInfo
     * controller concern): gateway rows merged into one object.
     */
    public static function basicPaymentInfo(): object
    {
        $info = [];
        foreach (['stripe', 'paypal', 'bank'] as $gateway) {
            foreach (static::gatewayConfig($gateway) as $key => $value) {
                $info[$key] = $value;
            }
        }

        return (object) $info;
    }

    public static function paymentGatewayInfo(): object
    {
        $info = [];
        foreach (['razorpay', 'mollie', 'instamojo', 'flutterwave', 'paystack', 'uddoktapay'] as $gateway) {
            foreach (static::gatewayConfig($gateway) as $key => $value) {
                $info[$key] = $value;
            }
        }

        return (object) $info;
    }

    /** Port of the old calculate_payable_charge(): amount + gateway % charge, converted to the session currency. */
    public static function calculatePayableCharge($payable_amount, $gateway_name): object
    {
        $gateway_charge = 0.0;
        $currency_rate = 1.0;
        $currency_code = getSessionCurrency() ?: 'USD';
        $country_code = 'US';
        $currency_id = 1;

        $basic = static::basicPaymentInfo();
        $setting = static::paymentGatewayInfo();
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

    /**
     * Config-only gateway hand-off (moved from PaymentController): resolves
     * the gateway and stores the pending-payment session state the old
     * addon controllers used. Returns the charge object, or null when the
     * gateway is not enabled. The real SDK charge/redirect is added when
     * the gateway SDK + credentials are configured.
     */
    public static function startGatewayPayment(string $gateway, $payable_amount): ?object
    {
        if (! static::isEnabled($gateway)) {
            return null;
        }

        $charge = static::calculatePayableCharge($payable_amount, $gateway);

        Session::put('after_success_url', route('payment-addon-success'));
        Session::put('after_faild_url', route('payment-addon-faild'));
        Session::put('after_success_gateway', ucfirst($gateway));
        Session::put('after_success_transaction', Str::random(10));
        Session::put('payable_with_charge', $charge->payable_with_charge);
        Session::put('payable_currency', $charge->currency_code);
        Session::put('paid_amount', $payable_amount);

        return $charge;
    }

    /**
     * Verify a Flutterwave transaction against the Flutterwave API.
     * Returns the payment-details array to store in the session, or null
     * when verification fails.
     */
    public static function verifyFlutterwave(string $tnxId): ?array
    {
        $config = static::gatewayConfig('flutterwave');
        $token = $config['flutterwave_secret_key'] ?? null;

        if (! $token || ! $tnxId) {
            return null;
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/$tnxId/verify",
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
            return [
                'status' => $response->status,
                'trx_id' => $tnxId,
                'amount' => $response?->data?->amount,
                'amount_settled' => $response?->data?->amount_settled,
                'currency' => $response?->data?->currency,
                'charged_amount' => $response?->data?->charged_amount,
                'app_fee' => $response?->data?->app_fee,
                'merchant_fee' => $response?->data?->merchant_fee,
                'card_last_4digits' => $response?->data?->card?->last_4digits,
            ];
        }

        return null;
    }

    /**
     * Verify a Paystack transaction against the Paystack API.
     * Returns the payment-details array to store in the session, or null
     * when verification fails.
     */
    public static function verifyPaystack(string $reference, string $secretKey, ?string $transaction): ?array
    {
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
            CURLOPT_HTTPHEADER => ["Authorization: Bearer $secretKey", 'Cache-Control: no-cache'],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        if ($err) {
            info($err);
        }
        curl_close($curl);
        $final_data = json_decode($response);

        if (($final_data->status ?? null) == true) {
            return [
                'status' => $final_data?->data?->status,
                'transaction_id' => $transaction,
                'requested_amount' => $final_data?->data->requested_amount,
                'amount' => $final_data?->data?->amount,
                'currency' => $final_data?->data?->currency,
                'gateway_response' => $final_data?->data?->gateway_response,
                'paid_at' => $final_data?->data?->paid_at,
                'card_last_4_digits' => $final_data?->data->authorization?->last4,
            ];
        }

        return null;
    }

    /** Order-completed mail (moved from PaymentController::handleMailSending). */
    public static function sendOrderCompletedMail(array $mailData): void
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
