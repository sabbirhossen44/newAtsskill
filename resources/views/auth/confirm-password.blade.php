@extends('frontend.layouts.master')
@section('meta_title', __('Confirm Password') . ' | ' . setting('app_name', 'ATS Skill'))
@section('contents')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">{{ __('Confirm Password') }}</h3>
                    <p class="text-muted text-center">{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</p>
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="password">{{ __('Password') }}</label>
                            <input type="password" class="form-control" id="password" name="password" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">{{ __('Confirm') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
