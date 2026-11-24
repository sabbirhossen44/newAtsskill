@extends('frontend.student-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Review Details') }}</h2>
    <div class="card"><div class="card-body">
        <h5>{{ $review->course->title ?? '' }}</h5>
        <p class="mb-1">{{ __('Rating') }}: {{ $review->rating ?? '' }}/5</p>
        <p>{{ $review->comment ?? $review->review ?? '' }}</p>
        <a href="{{ route('student.reviews.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
    </div></div>
@endsection
