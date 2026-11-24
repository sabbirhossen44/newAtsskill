@extends('admin.auth.app')
@section('title', __('Reset Password'))
@section('auth-content')
<form action="{{ route('admin.password.reset-store', $token ?? request()->route('token')) }}" method="POST">@csrf
    <div class="mb-3"><label class="form-label" for="email">{{ __('Email') }}</label><input type="email" name="email" id="email" value="{{ old('email', request('email')) }}" class="form-control" required></div>
    <div class="mb-3"><label class="form-label" for="password">{{ __('New Password') }}</label><input type="password" name="password" id="password" class="form-control" required></div>
    <div class="mb-3"><label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}</label><input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required></div>
    <button class="btn btn-primary w-100">{{ __('Reset Password') }}</button>
</form>
@endsection
