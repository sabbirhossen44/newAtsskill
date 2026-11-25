<?php

use App\Models\Language;
use App\Models\MultiCurrency;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return SettingService::get($key, $default);
    }
}

if (! function_exists('setSettingValue')) {
    function setSettingValue(string $key, mixed $value): Setting
    {
        return SettingService::set($key, $value);
    }
}

if (! function_exists('getSessionLanguage')) {
    function getSessionLanguage(): string
    {
        if (! session()->has('lang')) {
            session()->put('lang', config('app.locale'));
            session()->forget('text_direction');
            session()->put('text_direction', 'ltr');
        }

        return (string) Session::get('lang');
    }
}

if (! function_exists('getDefaultLanguage')) {
    function getDefaultLanguage(): string
    {
        $defaultLanguage = Cache::rememberForever('defaultLanguage', function () {
            try {
                return Language::query()->where('is_default', 1)->first()->code;
            } catch (\Throwable $e) {
                info($e->getMessage());

                return 'en';
            }
        });

        return $defaultLanguage;
    }
}

if (! function_exists('allCurrencies')) {
    function allCurrencies()
    {
        $allCurrencies = Cache::rememberForever('allCurrencies', function () {
            return MultiCurrency::all();
        });

        if (! $allCurrencies) {
            $allCurrencies = MultiCurrency::all();
        }

        return $allCurrencies;
    }
}

if (! function_exists('getSessionCurrency')) {
    function getSessionCurrency(): string
    {
        if (! session()->has('currency_code') || ! session()->has('currency_rate') || ! session()->has('currency_position')) {
            $currency = allCurrencies()->where('is_default', 'yes')->first();

            if ($currency) {
                session()->put('currency_code', $currency->currency_code);
                session()->forget('currency_position');
                session()->put('currency_position', $currency->currency_position);
                session()->forget('currency_icon');
                session()->put('currency_icon', $currency->currency_icon);
                session()->forget('currency_rate');
                session()->put('currency_rate', $currency->currency_rate);
            }
        }

        return (string) session('currency_code', config('app.currency', 'BDT'));
    }
}

/**
 * Set the tab step for the form.
 *
 * Port of the old helper (old app/Helpers/helper.php): the student and
 * instructor profile FormRequests and controllers call this, but the
 * function was missing from the rebuild.
 */
if (! function_exists('setFormTabStep')) {
    function setFormTabStep(string $name, string $step): void
    {
        session()->flash($name, $step);
    }
}
