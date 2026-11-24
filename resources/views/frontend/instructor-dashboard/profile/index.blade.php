@extends('frontend.instructor-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Profile Settings') }}</h2>
    @include('frontend.instructor-dashboard.profile.sections.profile')
    @include('frontend.instructor-dashboard.profile.sections.biography')
    @include('frontend.instructor-dashboard.profile.sections.education-and-experience')
    @include('frontend.instructor-dashboard.profile.sections.location')
    @include('frontend.instructor-dashboard.profile.sections.payout')
    @include('frontend.instructor-dashboard.profile.sections.social')
    @include('frontend.instructor-dashboard.profile.sections.password')
@endsection
