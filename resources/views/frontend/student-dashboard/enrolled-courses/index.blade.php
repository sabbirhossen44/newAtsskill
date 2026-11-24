@extends('frontend.student-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Enrolled Courses') }}</h2>
    <div class="row g-3">@forelse ($enrollments ?? $courses ?? [] as $enrollment)
        @php $course = $enrollment->course ?? $enrollment; @endphp
        <div class="col-md-6 col-xl-4"><div class="card h-100">
            <img src="{{ \App\Models\Media::resolveUrl($course->thumbnail ?? null) ?? asset('images/placeholder.svg') }}" class="card-img-top" alt="{{ $course->title ?? '' }}">
            <div class="card-body d-flex flex-column"><h5 class="card-title">{{ $course->title ?? '' }}</h5>
            <div class="progress mb-2" style="height:8px;"><div class="progress-bar" style="width: {{ $enrollment->progress ?? 0 }}%"></div></div>
            <small class="text-muted mb-3">{{ __('Progress') }}: {{ $enrollment->progress ?? 0 }}%</small>
            <div class="mt-auto d-flex gap-2"><a href="{{ route('student.learning.index', $course->slug ?? $course->id) }}" class="btn btn-primary btn-sm flex-grow-1">{{ __('Continue Learning') }}</a>
            <a href="{{ route('student.download-certificate', $course->id) }}" class="btn btn-outline-success btn-sm" title="{{ __('Download Certificate') }}"><i class="fas fa-certificate"></i></a></div>
            </div></div></div>
    @empty<div class="col-12"><div class="alert alert-info">{{ __('You have not enrolled in any course yet.') }} <a href="{{ route('courses') }}">{{ __('Browse Courses') }}</a></div></div>@endforelse</div>
@endsection
