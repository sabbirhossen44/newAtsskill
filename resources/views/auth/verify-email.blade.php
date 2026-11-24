@extends('frontend.layouts.master')
@section('meta_title', __('Verify Email') . ' | ' . setting('app_name', 'ATS Skill'))
@section('contents')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <h3 class="card-title mb-3">{{ __('Verify Your Email Address') }}</h3>
                    @if(session('status') === 'verification-link-sent')
                        <div class="alert alert-success">{{ __('A new verification link has been sent to your email address.') }}</div>
                    @endif
                    <p>{{ __('Before continuing, please check your email for a verification link.') }}</p>
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">{{ __('Resend Verification Email') }}</button>
                    </form>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-link">{{ __('Log Out') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
