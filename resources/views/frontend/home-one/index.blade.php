{{--
    Homepage — theme-one. Section order matches the live site (atsskill.com):
    hero, top categories, popular courses, newsletter, instructors, features,
    become instructor/student, testimonials, stats + admission, team, blog.
    Each section is gated on $sectionSettings (section_settings table) where
    the old theme gated it; team is always shown (old theme included it
    unconditionally).
--}}
@extends('frontend.layouts.master')

@section('meta_title', $seo_setting['home_page']['seo_title'] ?? setting('app_name', 'ATS Skill'))
@section('meta_description', $seo_setting['home_page']['seo_description'] ?? '')

@section('contents')
    @if ($sectionSettings?->hero_section ?? true)
        @include('frontend.home-one.sections.hero')
    @endif

    @if ($sectionSettings?->top_category_section ?? true)
        @include('frontend.home-one.sections.categories')
    @endif

    @if ($sectionSettings?->featured_course_section ?? true)
        @include('frontend.home-one.sections.courses')
    @endif

    @if ($sectionSettings?->news_letter_section ?? true)
        @include('frontend.home-one.sections.newsletter')
    @endif

    @if ($sectionSettings?->featured_instructor_section ?? true)
        @include('frontend.home-one.sections.instructors')
    @endif

    @if ($sectionSettings?->our_features_section ?? true)
        @include('frontend.home-one.sections.features')
    @endif

    @if ($sectionSettings?->banner_section ?? true)
        @include('frontend.home-one.sections.become')
    @endif

    @if ($sectionSettings?->testimonial_section ?? true)
        @include('frontend.home-one.sections.testimonials')
    @endif

    @if ($sectionSettings?->counter_section ?? true)
        @include('frontend.home-one.sections.stats')
        @include('frontend.home-one.sections.admission')
    @endif

    @include('frontend.home-one.sections.team')

    @if ($sectionSettings?->latest_blog_section ?? true)
        @include('frontend.home-one.sections.blog')
    @endif
@endsection
