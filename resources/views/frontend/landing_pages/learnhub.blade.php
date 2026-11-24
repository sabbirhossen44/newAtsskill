@extends('frontend.layouts.master')
@section('meta_title', __('LearnHub') . ' | ' . setting('app_name', 'ATS Skill'))
@section('contents')
    <section class="py-5 text-center"><div class="container">
        <h1 class="display-5 fw-bold">{{ $landingPage->title ?? __('LearnHub — Learn Anything, Anywhere') }}</h1>
        <p class="lead text-muted">{{ $landingPage->subtitle ?? __('A curated hub of the best courses on ATS Skill.') }}</p>
        <a href="{{ route('courses') }}" class="btn btn-primary btn-lg">{{ __('Start Learning') }}</a>
    </div></section>
    @include('frontend.home-one.sections.hero', ['hero' => $landingPage->hero ?? $hero ?? null])
    @include('frontend.home-one.sections.categories', ['categories' => $categories ?? []])
    @include('frontend.home-one.sections.courses', ['courses' => $courses ?? []])
    @include('frontend.home-one.sections.testimonials', ['testimonials' => $testimonials ?? []])
    @include('frontend.home-one.sections.blog', ['blogs' => $blogs ?? []])

@endsection
