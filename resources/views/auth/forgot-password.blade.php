@extends('frontend.layouts.master')
@section('meta_title', __('Forgot Password') . ' | ' . setting('app_name', 'ATS Skill'))
@section('contents')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">{{ __('Forgot Password') }}</h3>
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="email">{{ __('Email') }}</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">{{ __('Email Password Reset Link') }}</button>
                    </form>
                    <p class="text-center mt-3"><a href="{{ route('login') }}">{{ __('Back to Login') }}</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
