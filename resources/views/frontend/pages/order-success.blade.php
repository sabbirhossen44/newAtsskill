{{-- Order success (route: order-success). $order optional (invoice_id). --}}
@extends('frontend.layouts.master')
@section('meta_title', __('Order Completed') . ' | ' . setting('app_name', 'ATS Skill'))

@section('contents')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 text-center">
                    <div class="card p-5">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size:3.5rem;"></i>
                        <h1 class="h3 fw-bold">{{ __('Order Completed!') }}</h1>
                        <p class="text-muted">{{ __('Thank you! Your payment was successful and your courses have been enrolled.') }}</p>
                        @if ($order->invoice_id ?? null)<p>{{ __('Invoice ID:') }} <strong>{{ $order->invoice_id }}</strong></p>@endif
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <a href="{{ route('student.enrolled-courses') }}" class="btn btn-primary">{{ __('Go to My Courses') }}</a>
                            <a href="{{ route('courses') }}" class="btn btn-outline-primary">{{ __('Browse More Courses') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
