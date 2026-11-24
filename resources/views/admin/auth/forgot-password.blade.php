@extends('admin.auth.app')
@section('title', __('Forgot Password'))
@section('auth-content')
<p class="text-muted">{{ __('Enter your admin email and we will send a password reset link.') }}</p>
<form action="{{ route('admin.forget-password') }}" method="POST">@csrf
    <div class="mb-3"><label class="form-label" for="email">{{ __('Email') }}</label><input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control" required></div>
    <button class="btn btn-primary w-100">{{ __('Send Reset Link') }}</button>
    <div class="text-center mt-3"><a href="{{ route('admin.login') }}">{{ __('Back to login') }}</a></div>
</form>
@endsection
