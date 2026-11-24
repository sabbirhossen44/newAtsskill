{{--
    Student dashboard home — SKELETON with real data hooks.
    $stats (optional): enrolled_courses, total_orders, quiz_attempts,
    wallet_balance. $recentEnrollments: enrollments with course.
    Full sub-pages (orders, enrolled courses, reviews, quiz attempts,
    profile settings) are separate views to be built on this shell.
--}}
@extends('frontend.student-dashboard.layouts.master')

@section('dashboard-contents')
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card ats-stat-card p-3 text-center">
                <h3 class="fw-bold text-primary mb-0">{{ $stats['enrolled_courses'] ?? 0 }}</h3>
                <p class="mb-0 text-muted">{{ __('Enrolled Courses') }}</p>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card ats-stat-card p-3 text-center">
                <h3 class="fw-bold text-primary mb-0">{{ $stats['total_orders'] ?? 0 }}</h3>
                <p class="mb-0 text-muted">{{ __('Total Orders') }}</p>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card ats-stat-card p-3 text-center">
                <h3 class="fw-bold text-primary mb-0">{{ $stats['quiz_attempts'] ?? 0 }}</h3>
                <p class="mb-0 text-muted">{{ __('Quiz Attempts') }}</p>
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
            <h3 class="h5 fw-bold mb-0">{{ __('My Enrolled Courses') }}</h3>
            <a href="{{ route('student.enrolled-courses') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
        </div>
        @if (!empty($recentEnrollments) && $recentEnrollments->count())
            <div class="row g-4">
                @foreach ($recentEnrollments as $enrollment)
                    @if ($enrollment->course)
                        <div class="col-md-6 col-xl-4">@include('frontend.partials.course-card', ['course' => $enrollment->course])</div>
                    @endif
                @endforeach
            </div>
        @else
            <p class="text-muted mb-0">{{ __('You have not enrolled in any course yet.') }} <a href="{{ route('courses') }}">{{ __('Browse courses') }}</a></p>
        @endif
    </div>
@endsection
