@extends('frontend.layouts.master')
@section('meta_title', __('ATS Skill — Admission Open') . ' | ' . setting('app_name', 'ATS Skill'))
@section('contents')
    <section class="py-5" style="background:#eff6ff;"><div class="container"><div class="row align-items-center g-4">
        <div class="col-lg-6"><h1 class="display-5 fw-bold">{{ $landingPage->title ?? __('Admission Open — New Batches') }}</h1><p class="lead text-muted">{{ $landingPage->subtitle ?? __('Enroll now and start learning with live classes and mentorship.') }}</p><a href="{{ route('courses') }}" class="btn btn-primary btn-lg">{{ __('Enroll Now') }}</a></div>
        <div class="col-lg-6"><img src="{{ asset('images/placeholder.svg') }}" class="img-fluid rounded" alt="{{ __('Admission') }}"></div>
    </div></div></section>
    @include('frontend.home-one.sections.hero', ['hero' => $landingPage->hero ?? $hero ?? null])
    @include('frontend.home-one.sections.categories', ['categories' => $categories ?? []])
    @include('frontend.home-one.sections.courses', ['courses' => $courses ?? []])
    @include('frontend.home-one.sections.testimonials', ['testimonials' => $testimonials ?? []])
    @include('frontend.home-one.sections.blog', ['blogs' => $blogs ?? []])

@endsection
