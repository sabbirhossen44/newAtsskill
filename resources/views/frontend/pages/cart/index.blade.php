{{--
    Cart. $cartItems: each item has rowId, id, name, slug, price, qty,
    options.image. $cartTotal / $cartCount. Routes (old names kept):
    remove-cart-item, apply-coupon, remove-coupon, checkout.index.
    Warns when an item is already enrolled or belongs to the instructor.
--}}
@extends('frontend.layouts.master')
@section('meta_title', __('Cart') . ' | ' . setting('app_name', 'ATS Skill'))

@section('contents')
    @include('frontend.partials.breadcrumb', ['title' => __('Cart'), 'links' => [['url' => route('home'), 'text' => __('Home')], ['url' => '', 'text' => __('Cart')]]])

    <section class="cart__area py-5">
        <div class="container">
            @foreach ($cartItems ?? [] as $item)
                @if (in_array($item['id'], session('enrollments', [])))
                    <div class="alert alert-danger">{{ __('You have items in your cart that you already purchased. Before proceeding please remove those from cart.') }}</div>
                @elseif (in_array($item['id'], session('instructor_courses', [])))
                    <div class="alert alert-danger">{{ __('You have your own courses in your cart. Before proceeding please remove those from cart.') }}</div>
                @endif
            @endforeach

            @if (!empty($cartItems) && count($cartItems) > 0)
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card p-3">
                            <div class="table-responsive">
                                <table class="table cart__table align-middle">
                                    <thead>
                                        <tr><th>&nbsp;</th><th>{{ __('Course') }}</th><th>{{ __('Price') }}</th><th>&nbsp;</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cartItems as $item)
                                            <tr>
                                                <td style="width:110px;">
                                                    <a href="{{ route('course.show', $item['slug']) }}">
                                                        <img src="{{ \App\Models\Media::resolveUrl($item['options']['image'] ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ $item['name'] }}" class="img-fluid rounded" style="max-height:64px;object-fit:cover;">
                                                    </a>
                                                </td>
                                                <td><a href="{{ route('course.show', $item['slug']) }}" class="fw-semibold">{{ $item['name'] }}</a></td>
                                                <td>৳{{ number_format($item['price'], 0) }}</td>
                                                <td><a href="{{ route('remove-cart-item', ['rowId' => $item['rowId']]) }}" class="text-danger" aria-label="{{ __('Remove') }}"><i class="fas fa-trash-alt"></i></a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card p-3 mt-3">
                            <h5 class="h6 fw-bold">{{ __('Have a coupon?') }}</h5>
                            @if (session('coupon'))
                                <p class="mb-2">{{ __('Coupon applied:') }} <strong>{{ session('coupon')['code'] ?? '' }}</strong>
                                    <a href="{{ route('remove-coupon') }}" class="text-danger ms-2">{{ __('Remove') }}</a></p>
                            @else
                                <form action="{{ route('apply-coupon') }}" method="POST" class="d-flex gap-2">
                                    @csrf
                                    <input type="text" name="coupon_code" class="form-control" placeholder="{{ __('Coupon code') }}" required>
                                    <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card p-3 position-sticky" style="top:90px;">
                            <h5 class="fw-bold">{{ __('Cart Totals') }}</h5>
                            <ul class="list-unstyled">
                                <li class="d-flex justify-content-between"><span>{{ __('Subtotal') }}</span><strong>৳{{ number_format($cartSubtotal ?? $cartTotal ?? 0, 0) }}</strong></li>
                                @if (($cartDiscount ?? 0) > 0)
                                    <li class="d-flex justify-content-between text-success"><span>{{ __('Coupon discount') }}</span><strong>− ৳{{ number_format($cartDiscount, 0) }}</strong></li>
                                @endif
                                <li class="d-flex justify-content-between border-top pt-2 mt-2"><span>{{ __('Total') }}</span><strong>৳{{ number_format($cartTotal ?? 0, 0) }}</strong></li>
                            </ul>
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary w-100">{{ __('Proceed to Checkout') }}</a>
                            <a href="{{ route('courses') }}" class="btn btn-link w-100">{{ __('Continue Shopping') }}</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <p class="fs-5">{{ __('Your cart is empty.') }}</p>
                    <a href="{{ route('courses') }}" class="btn arrow-btn">{{ __('Browse Courses') }}</a>
                </div>
            @endif
        </div>
    </section>
@endsection
