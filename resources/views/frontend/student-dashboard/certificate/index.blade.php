@extends('frontend.student-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Certificate') }}</h2>
    <div class="card"><div class="card-body text-center py-5">
        <i class="fas fa-certificate fa-3x text-warning mb-3"></i>
        <h5>{{ $course->title ?? '' }}</h5>
        <p class="text-muted">{{ __('Download your course completion certificate.') }}</p>
        @isset($course)<a href="{{ route('student.download-certificate', $course->id) }}" class="btn btn-primary"><i class="fas fa-download me-1"></i>{{ __('Download Certificate') }}</a>@endisset
        <a href="{{ route('student.download-demo-certificate') }}" class="btn btn-outline-secondary">{{ __('Download Demo Certificate') }}</a>
    </div></div>
@endsection
