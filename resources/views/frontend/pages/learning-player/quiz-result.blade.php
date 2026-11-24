@extends('frontend.pages.learning-player.master')
@section('learning-contents')
<section class="py-5"><div class="container"><div class="row justify-content-center"><div class="col-lg-6">
    <div class="card text-center"><div class="card-body py-5">
        <h2 class="h4">{{ __('Quiz Result') }}</h2>
        <p class="display-6">{{ $result->obtained_marks ?? $result->score ?? 0 }} / {{ $result->total_marks ?? 0 }}</p>
        <p><span class="badge {{ ($result->status ?? '') == 'passed' ? 'bg-success' : 'bg-danger' }} fs-6">{{ $result->status ?? '' }}</span></p>
        <a href="{{ route('student.quiz-attempts') }}" class="btn btn-outline-primary">{{ __('My Quiz Attempts') }}</a>
        <a href="{{ route('student.dashboard') }}" class="btn btn-primary">{{ __('Back to Dashboard') }}</a>
    </div></div></div></div></div></section>
@endsection
