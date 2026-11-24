@extends('admin.master_layout')
@section('title')
    <title>Basic Payments | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <h1 class="h4 mb-3">{{ __('Basic Payments') }}</h1>
    <div class="row g-3">
    <div class="col-lg-4"><div class="card h-100"><div class="card-body"><h5>Stripe</h5>
        <form action="{{ route('admin.update-stripe') }}" method="POST">@csrf @method('PUT')
        <div class="mb-2"><label class="form-label">{{ __('Status') }}</label><select name="status" class="form-select"><option value="active">{{ __('Active') }}</option><option value="inactive">{{ __('Inactive') }}</option></select></div>
        <div class="mb-2"><label class="form-label">{{ __('Publishable Key') }}</label><input type="text" name="stripe_key" class="form-control" autocomplete="off"></div>
        <button class="btn btn-primary btn-sm">{{ __('Save Stripe') }}</button></form></div></div></div>
    <div class="col-lg-4"><div class="card h-100"><div class="card-body"><h5>PayPal</h5>
        <form action="{{ route('admin.update-paypal') }}" method="POST">@csrf @method('PUT')
        <div class="mb-2"><label class="form-label">{{ __('Status') }}</label><select name="status" class="form-select"><option value="active">{{ __('Active') }}</option><option value="inactive">{{ __('Inactive') }}</option></select></div>
        <div class="mb-2"><label class="form-label">{{ __('Client ID') }}</label><input type="text" name="paypal_client_id" class="form-control" autocomplete="off"></div>
        <button class="btn btn-primary btn-sm">{{ __('Save PayPal') }}</button></form></div></div></div>
    <div class="col-lg-4"><div class="card h-100"><div class="card-body"><h5>{{ __('Direct Bank') }}</h5>
        <form action="{{ route('admin.update-bank-payment') }}" method="POST">@csrf @method('PUT')
        <div class="mb-2"><label class="form-label">{{ __('Status') }}</label><select name="status" class="form-select"><option value="active">{{ __('Active') }}</option><option value="inactive">{{ __('Inactive') }}</option></select></div>
        <div class="mb-2"><label class="form-label">{{ __('Bank Details') }}</label><textarea name="bank_details" rows="4" class="form-control">{{ old('bank_details', $basicPayment->bank_details ?? '') }}</textarea></div>
        <button class="btn btn-primary btn-sm">{{ __('Save Bank') }}</button></form></div></div></div>
    </div>
@endsection
