@extends('frontend.layouts.master')
@section('meta_title', __('ATS Skill — Learn & Earn') . ' | ' . setting('app_name', 'ATS Skill'))
@section('contents')
    <section class="py-5 text-center"><div class="container">
        <h1 class="display-5 fw-bold">{{ $landingPage->title ?? __('Learn Skills, Build Your Career') }}</h1>
        <p class="lead text-muted">{{ $landingPage->subtitle ?? __('Practical courses in Bangla, taught by industry experts.') }}</p>
        <div class="d-flex gap-2 justify-content-center"><a href="{{ route('courses') }}" class="btn btn-primary btn-lg">{{ __('Browse Courses') }}</a><a href="{{ route('become-instructor') }}" class="btn btn-outline-primary btn-lg">{{ __('Become an Instructor') }}</a></div>
    </div></section>
    @include('frontend.home-one.sections.hero', ['hero' => $landingPage->hero ?? $hero ?? null])
    @include('frontend.home-one.sections.categories', ['categories' => $categories ?? []])
    @include('frontend.home-one.sections.courses', ['courses' => $courses ?? []])
    @include('frontend.home-one.sections.testimonials', ['testimonials' => $testimonials ?? []])
    @include('frontend.home-one.sections.blog', ['blogs' => $blogs ?? []])

@endsection
