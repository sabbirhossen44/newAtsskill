<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CheckoutService;

class CheckOutController extends Controller
{
    protected CheckoutService $checkout;

    public function __construct(CheckoutService $checkout)
    {
        $this->checkout = $checkout;
    }

    public function index()
    {
        if ($this->checkout->hasAlreadyEnrolledProduct()) {
            return redirect()->route('cart')->with(['messege' => __('Error occurred please try agin'), 'alert-type' => 'error']);
        }

        return view('frontend.pages.checkout.index')->with($this->checkout->checkoutData());
    }
}
