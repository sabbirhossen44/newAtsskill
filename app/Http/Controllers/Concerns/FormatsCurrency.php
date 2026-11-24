<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Session;

/**
 * Port of the old global currency() helper (old app/Helpers/helper.php).
 */
trait FormatsCurrency
{
    protected function formatCurrency($price): string
    {
        getSessionCurrency();
        $currency_icon = Session::get('currency_icon');
        $currency_rate = Session::has('currency_rate') ? Session::get('currency_rate') : 1;
        $currency_position = Session::get('currency_position');

        $price = $price * $currency_rate;
        $price = number_format($price, 2, '.', ',');

        if ($currency_position == 'before_price') {
            $price = $currency_icon.$price;
        } elseif ($currency_position == 'before_price_with_space') {
            $price = $currency_icon.' '.$price;
        } elseif ($currency_position == 'after_price') {
            $price = $price.$currency_icon;
        } elseif ($currency_position == 'after_price_with_space') {
            $price = $price.' '.$currency_icon;
        } else {
            $price = $currency_icon.$price;
        }

        return $price;
    }
}
