@extends('admin.master_layout')
@section('title')
    <title>Credentials | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="card"><div class="card-body"><p class="text-muted">{{ __('Third-party credentials (cloud storage, social login, analytics, chat, captcha, pixel, tag manager). Secrets are stored in configuration — never displayed in full here.') }}</p>
    <div class="row g-3">
    <div class="col-md-6"><div class="border rounded p-3"><h6>{{ __('AWS / Wasabi') }}</h6><form action="{{ route('admin.update-aws-cloud') }}" method="POST">@csrf @method('PUT')<div class="mb-2"><input type="text" name="aws_bucket" class="form-control" placeholder="{{ __('Bucket') }}"></div><button class="btn btn-sm btn-outline-primary">{{ __('Save') }}</button></form></div></div>
    <div class="col-md-6"><div class="border rounded p-3"><h6>{{ __('Social Login') }}</h6><form action="{{ route('admin.update-social-login') }}" method="POST">@csrf @method('PUT')<div class="mb-2"><input type="text" name="google_client_id" class="form-control" placeholder="{{ __('Google Client ID') }}"></div><button class="btn btn-sm btn-outline-primary">{{ __('Save') }}</button></form></div></div>
    <div class="col-md-6"><div class="border rounded p-3"><h6>{{ __('Google Analytics') }}</h6><form action="{{ route('admin.update-google-analytic') }}" method="POST">@csrf @method('PUT')<div class="mb-2"><input type="text" name="google_analytics_id" class="form-control" placeholder="{{ __('Measurement ID') }}"></div><button class="btn btn-sm btn-outline-primary">{{ __('Save') }}</button></form></div></div>
    <div class="col-md-6"><div class="border rounded p-3"><h6>{{ __('Facebook Pixel') }}</h6><form action="{{ route('admin.update-facebook-pixel') }}" method="POST">@csrf @method('PUT')<div class="mb-2"><input type="text" name="facebook_pixel_id" class="form-control" placeholder="{{ __('Pixel ID') }}"></div><button class="btn btn-sm btn-outline-primary">{{ __('Save') }}</button></form></div></div>
    <div class="col-md-6"><div class="border rounded p-3"><h6>{{ __('Google reCAPTCHA') }}</h6><form action="{{ route('admin.update-google-captcha') }}" method="POST">@csrf @method('PUT')<div class="mb-2"><input type="text" name="recaptcha_site_key" class="form-control" placeholder="{{ __('Site Key') }}"></div><button class="btn btn-sm btn-outline-primary">{{ __('Save') }}</button></form></div></div>
    <div class="col-md-6"><div class="border rounded p-3"><h6>{{ __('Tawk Chat') }}</h6><form action="{{ route('admin.update-tawk-chat') }}" method="POST">@csrf @method('PUT')<div class="mb-2"><input type="text" name="tawk_property_id" class="form-control" placeholder="{{ __('Property ID') }}"></div><button class="btn btn-sm btn-outline-primary">{{ __('Save') }}</button></form></div></div>
    </div></div></div>
@endsection
