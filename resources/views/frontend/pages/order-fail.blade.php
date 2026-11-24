{{-- Order failed (route: order-fail). --}}
@extends('frontend.layouts.master')
@section('meta_title', __('Order Failed') . ' | ' . setting('app_name', 'ATS Skill'))

@section('contents')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 text-center">
                    <div class="card p-5">
                        <i class="fas fa-times-circle text-danger mb-3" style="font-size:3.5rem;"></i>
                        <h1 class="h3 fw-bold">{{ __('Payment Failed') }}</h1>
                        <p class="text-muted">{{ __('Your payment could not be completed. No amount was charged for the failed attempt — please try again or choose another payment method.') }}</p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary">{{ __('Try Again') }}</a>
                            <a href="{{ route('contact.index') }}" class="btn btn-outline-primary">{{ __('Contact Support') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
