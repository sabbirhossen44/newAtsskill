@extends('frontend.layouts.master')
@section('meta_title', __('Login') . ' | ' . setting('app_name', 'ATS Skill'))
@section('contents')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">{{ __('Login') }}</h3>
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    <form method="POST" action="{{ route('user-login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="email">{{ __('Email') }}</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">{{ __('Password') }}</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">{{ __('Remember me') }}</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">{{ __('Login') }}</button>
                    </form>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
                        <a href="{{ route('register') }}">{{ __('Register') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
