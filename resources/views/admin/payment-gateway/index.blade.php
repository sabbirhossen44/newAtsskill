@extends('admin.master_layout')
@section('title')
    <title>Payment Gateways | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <h1 class="h4 mb-3">{{ __('Payment Gateways') }}</h1>
    <p class="text-muted">{{ __('Gateway credentials live in configuration / environment — this page only toggles status and stores public settings. No secret is ever rendered here.') }}</p>
    <div class="row g-3">
    @foreach ([['razorpay', 'Razorpay', 'admin.razorpay-update'], ['flutterwave', 'Flutterwave', 'admin.flutterwave-update'], ['paystack', 'Paystack', 'admin.paystack-update'], ['mollie', 'Mollie', 'admin.mollie-update'], ['instamojo', 'Instamojo', 'admin.instamojo-update'], ['uddoktapay', 'UddoktaPay', 'admin.uddoktapay-update']] as [$key, $label, $route])
    <div class="col-md-6"><div class="card h-100"><div class="card-body">
        <h5>{{ $label }}</h5>
        <form action="{{ route($route) }}" method="POST">@csrf @method('PUT')
            <div class="mb-2"><label class="form-label">{{ __('Status') }}</label><select name="status" class="form-select"><option value="active" {{ ($gateways[$key]->status ?? '') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option><option value="inactive" {{ ($gateways[$key]->status ?? '') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option></select></div>
            <div class="mb-2"><label class="form-label">{{ __('Public Key / Client ID') }}</label><input type="text" name="public_key" value="{{ $gateways[$key]->public_key ?? '' }}" class="form-control" autocomplete="off"></div>
            <button class="btn btn-primary btn-sm">{{ __('Save') }}</button></form>
    </div></div></div>
    @endforeach
    </div>
    <div class="card mt-3"><div class="card-body"><h5>{{ __('Local Gateways (bKash / Nagad / Rocket / SSLCOMMERZ)') }}</h5>
    <p class="text-muted mb-0">{{ __('These are managed under Basic Payments / gateway configuration and shown on checkout as display-only cards. See the checkout view for the posting routes (bkash, payment with gateway=nagad/rocket/sslcommerz).') }}</p></div></div>
@endsection
