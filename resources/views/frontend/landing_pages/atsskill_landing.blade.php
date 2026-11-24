@extends('frontend.layouts.master')
@section('meta_title', __('ATS Skill') . ' | ' . setting('app_name', 'ATS Skill'))
@section('contents')
    @include('frontend.home-one.sections.hero', ['hero' => $landingPage->hero ?? $hero ?? null])
    @include('frontend.home-one.sections.categories', ['categories' => $categories ?? []])
    @include('frontend.home-one.sections.courses', ['courses' => $courses ?? []])
    @include('frontend.home-one.sections.testimonials', ['testimonials' => $testimonials ?? []])
    @include('frontend.home-one.sections.blog', ['blogs' => $blogs ?? []])

@endsection
