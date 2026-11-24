@extends('frontend.layouts.master')
@section('meta_title', __('Checkout') . ' | ' . setting('app_name', 'ATS Skill'))
@section('contents')
    <section class="py-5"><div class="container"><div class="row justify-content-center"><div class="col-lg-8">
        <div class="card"><div class="card-body text-center py-5">
        <h1 class="h3">{{ $landingPage->title ?? __('Complete Your Enrollment') }}</h1>
        <p class="text-muted">{{ __('You are one step away from starting your course.') }}</p>
        <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">{{ __('Go to Checkout') }}</a>
        </div></div></div></div></div></section>

@endsection
