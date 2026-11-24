@extends('admin.auth.app')
@section('title', __('Admin Login'))
@section('auth-content')
<form action="{{ route('admin.store-login') }}" method="POST">@csrf
    <div class="mb-3"><label class="form-label" for="email">{{ __('Email') }}</label><input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control" required autofocus></div>
    <div class="mb-3"><label class="form-label" for="password">{{ __('Password') }}</label><input type="password" name="password" id="password" class="form-control" required></div>
    <div class="mb-3 form-check"><input type="checkbox" name="remember" id="remember" class="form-check-input"><label class="form-check-label" for="remember">{{ __('Remember me') }}</label></div>
    <button class="btn btn-primary w-100">{{ __('Login') }}</button>
    <div class="text-center mt-3"><a href="{{ route('admin.password.request') }}">{{ __('Forgot your password?') }}</a></div>
</form>
@endsection
