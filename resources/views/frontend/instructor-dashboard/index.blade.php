{{--
    Instructor dashboard home — SKELETON with real data hooks.
    $stats (optional): total_courses, total_students, total_sales,
    wallet_balance. $recentOrders: orders with items/course.
    Full sub-pages (course builder, lesson Q&A, payout, announcements,
    my sells, live settings, profile) are separate views on this shell.
--}}
@extends('frontend.instructor-dashboard.layouts.master')

@section('dashboard-contents')
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card ats-stat-card p-3 text-center">
                <h3 class="fw-bold text-primary mb-0">{{ $stats['total_courses'] ?? 0 }}</h3>
                <p class="mb-0 text-muted">{{ __('Total Courses') }}</p>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card ats-stat-card p-3 text-center">
                <h3 class="fw-bold text-primary mb-0">{{ $stats['total_students'] ?? 0 }}</h3>
                <p class="mb-0 text-muted">{{ __('Total Students') }}</p>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card ats-stat-card p-3 text-center">
                <h3 class="fw-bold text-primary mb-0">৳{{ number_format($stats['total_sales'] ?? 0, 0) }}</h3>
                <p class="mb-0 text-muted">{{ __('Total Sales') }}</p>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card ats-stat-card p-3 text-center">
                <h3 class="fw-bold text-primary mb-0">৳{{ number_format($stats['wallet_balance'] ?? auth()->user()->wallet_balance ?? 0, 0) }}</h3>
                <p class="mb-0 text-muted">{{ __('Wallet Balance') }}</p>
            </div>
        </div>
    </div>

    <div class="card p-3 p-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 fw-bold mb-0">{{ __('Recent Sales') }}</h3>
            <a href="{{ route('instructor.my-sells.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
        </div>
        @if (!empty($recentOrders) && $recentOrders->count())
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>{{ __('Order') }}</th><th>{{ __('Course') }}</th><th>{{ __('Student') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Date') }}</th></tr></thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr>
                                <td>{{ $order->invoice_id ?? $order->id }}</td>
                                <td>{{ $order->items->first()?->course->title ?? '—' }}</td>
                                <td>{{ $order->buyer->name ?? '—' }}</td>
                                <td>৳{{ number_format($order->paid_amount ?? $order->payable_amount ?? 0, 0) }}</td>
                                <td>{{ $order->created_at?->format('d M, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">{{ __('No sales yet.') }} <a href="{{ route('instructor.courses.create') }}">{{ __('Create your first course') }}</a></p>
        @endif
    </div>
@endsection
