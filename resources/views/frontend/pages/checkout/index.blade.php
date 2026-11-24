{{--
    Checkout — gateway cards + coupon + order summary with the old payment flow's route names.
    $cartItems, $cartTotal, $cartDiscount, $payableAmount.
    Payment methods are display + form targets only. Gateway credentials
    (bKash / Nagad / Rocket / SSLCOMMERZ / Stripe / PayPal / bank) live in
    .env and the payment_gateways/basic_payments tables — NEVER in views.
    Old route names kept: payment, pay-via-stripe, pay-via-paypal,
    pay-via-bank, pay-via-razorpay, pay-via-mollie, pay-via-instamojo,
    pay-via-uddoktapay, pay-via-flutterwave, pay-via-paystack,
    pay-via-free-gateway, bkash, bkash-create-payment, order-success,
    order-fail. Controllers must wire each method's server-side flow;
    this view only collects the choice and posts to the matching route.
--}}
@extends('frontend.layouts.master')
@section('meta_title', __('Checkout') . ' | ' . setting('app_name', 'ATS Skill'))

@section('contents')
    @include('frontend.partials.breadcrumb', ['title' => __('Make Payment'), 'links' => [['url' => route('home'), 'text' => __('Home')], ['url' => route('cart'), 'text' => __('Cart')], ['url' => '', 'text' => __('Checkout')]]])

    <section class="py-5">
        <div class="container">
            @if (empty($cartItems) || count($cartItems) === 0)
                <div class="text-center py-5">
                    <p class="fs-5">{{ __('Your cart is empty.') }}</p>
                    <a href="{{ route('courses') }}" class="btn arrow-btn">{{ __('Browse Courses') }}</a>
                </div>
            @else
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card p-3 p-md-4 mb-4">
                            <h3 class="h5 fw-bold mb-3">{{ __('Billing Information') }}</h3>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">{{ __('Name') }}</label><input type="text" class="form-control" value="{{ auth()->user()->name ?? '' }}" readonly></div>
                                <div class="col-md-6"><label class="form-label">{{ __('Email') }}</label><input type="email" class="form-control" value="{{ auth()->user()->email ?? '' }}" readonly></div>
                                <div class="col-md-6"><label class="form-label">{{ __('Phone') }}</label><input type="text" class="form-control" value="{{ auth()->user()->phone ?? '' }}" readonly></div>
                                <div class="col-md-6"><label class="form-label">{{ __('Address') }}</label><input type="text" class="form-control" value="{{ auth()->user()->address ?? '' }}" readonly></div>
                            </div>
                            <p class="text-muted small mt-3 mb-0">{{ __('Update billing details from your profile settings.') }} <a href="{{ route('student.setting.index') }}">{{ __('Profile Settings') }}</a></p>
                        </div>

                        <div class="card p-3 p-md-4">
                            <h3 class="h5 fw-bold mb-3">{{ __('Payment Method') }}</h3>
                            <p class="text-muted small">{{ __('Choose a payment method. You will be redirected to the secure gateway to complete payment.') }}</p>
                            <div class="wsus__payment_area">
                                <div class="row g-3">
                                    {{-- bKash (tokenized) — GET route('bkash'), create via POST route('bkash-create-payment') --}}
                                    <div class="col-6 col-md-4">
                                        <div class="wsus__single_payment card text-center p-3 h-100">
                                            <strong class="d-block mb-2" style="color:#d1205c;">bKash</strong>
                                            <a href="{{ route('bkash') }}" class="btn btn-outline-primary btn-sm payment-card-btn">{{ __('Pay with bKash') }}</a>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="wsus__single_payment card text-center p-3 h-100">
                                            <strong class="d-block mb-2" style="color:#ee4023;">Nagad</strong>
                                            <form action="{{ route('payment') }}" method="GET"><input type="hidden" name="gateway" value="nagad"><button class="btn btn-outline-primary btn-sm" type="submit">{{ __('Pay with Nagad') }}</button></form>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="wsus__single_payment card text-center p-3 h-100">
                                            <strong class="d-block mb-2" style="color:#8c3494;">Rocket</strong>
                                            <form action="{{ route('payment') }}" method="GET"><input type="hidden" name="gateway" value="rocket"><button class="btn btn-outline-primary btn-sm" type="submit">{{ __('Pay with Rocket') }}</button></form>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="wsus__single_payment card text-center p-3 h-100">
                                            <strong class="d-block mb-2" style="color:#0f7a5c;">SSLCOMMERZ</strong>
                                            <form action="{{ route('payment') }}" method="GET"><input type="hidden" name="gateway" value="sslcommerz"><button class="btn btn-outline-primary btn-sm" type="submit">{{ __('Pay with SSLCOMMERZ') }}</button></form>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="wsus__single_payment card text-center p-3 h-100">
                                            <strong class="d-block mb-2">Stripe</strong>
                                            <form action="{{ route('pay-via-stripe') }}" method="POST">@csrf<button class="btn btn-outline-primary btn-sm" type="submit">{{ __('Pay with Stripe') }}</button></form>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="wsus__single_payment card text-center p-3 h-100">
                                            <strong class="d-block mb-2">PayPal</strong>
                                            <a href="{{ route('pay-via-paypal') }}" class="btn btn-outline-primary btn-sm">{{ __('Pay with PayPal') }}</a>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="wsus__single_payment card text-center p-3 h-100">
                                            <strong class="d-block mb-2">{{ __('Bank Transfer') }}</strong>
                                            <form action="{{ route('pay-via-bank') }}" method="POST">@csrf<button class="btn btn-outline-primary btn-sm" type="submit">{{ __('Pay via Bank') }}</button></form>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="wsus__single_payment card text-center p-3 h-100">
                                            <strong class="d-block mb-2">UddoktaPay</strong>
                                            <a href="{{ route('pay-via-uddoktapay') }}" class="btn btn-outline-primary btn-sm">{{ __('Pay with UddoktaPay') }}</a>
                                        </div>
                                    </div>
                                    @if (($payableAmount ?? $cartTotal ?? 0) == 0)
                                        <div class="col-6 col-md-4">
                                            <div class="wsus__single_payment card text-center p-3 h-100">
                                                <strong class="d-block mb-2">{{ __('Free') }}</strong>
                                                <form action="{{ route('pay-via-free-gateway') }}" method="POST">@csrf<button class="btn btn-success btn-sm" type="submit">{{ __('Enroll Free') }}</button></form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-muted small mt-3 mb-0">{{ __('Other gateways from the old flow (Razorpay, Mollie, Instamojo, Flutterwave, Paystack) are kept as routes pay-via-razorpay / pay-via-mollie / pay-via-instamojo / pay-via-flutterwave / pay-via-paystack — enable them here only after their merchant accounts are confirmed.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card p-3 position-sticky" style="top:90px;">
                            <h3 class="h5 fw-bold mb-3">{{ __('Order Summary') }}</h3>
                            @foreach ($cartItems as $item)
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>{{ $item['name'] }}</span>
                                    <strong>৳{{ number_format($item['price'], 0) }}</strong>
                                </div>
                            @endforeach
                            <form action="{{ route('apply-coupon') }}" method="POST" class="mt-3">@csrf
                                <label class="form-label">{{ __('Coupon Code') }}</label>
                                <div class="input-group"><input type="text" name="code" value="{{ session('coupon_code') ?? '' }}" class="form-control" placeholder="{{ __('Enter coupon code') }}"><button class="btn btn-outline-primary">{{ __('Apply') }}</button></div>
                            </form>
                            @if (session('coupon_code'))
                            <form action="{{ route('remove-coupon') }}" method="POST" class="mt-2">@csrf<button class="btn btn-link btn-sm text-danger p-0">{{ __('Remove coupon') }} ({{ session('coupon_code') }})</button></form>
                            @endif
                            <ul class="list-unstyled mt-3">
                                @if (($cartDiscount ?? 0) > 0)
                                    <li class="d-flex justify-content-between text-success"><span>{{ __('Coupon discount') }}</span><strong>− ৳{{ number_format($cartDiscount, 0) }}</strong></li>
                                @endif
                                <li class="d-flex justify-content-between border-top pt-2 mt-2"><span>{{ __('Payable amount') }}</span><strong>৳{{ number_format($payableAmount ?? $cartTotal ?? 0, 0) }}</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
