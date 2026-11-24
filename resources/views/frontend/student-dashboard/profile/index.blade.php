@extends('frontend.student-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Profile Settings') }}</h2>
    @include('frontend.student-dashboard.profile.sections.profile')
    @include('frontend.student-dashboard.profile.sections.biography')
    @include('frontend.student-dashboard.profile.sections.education-and-experience')
    @include('frontend.student-dashboard.profile.sections.location')
    @include('frontend.student-dashboard.profile.sections.social')
    @include('frontend.student-dashboard.profile.sections.password')
    @include('frontend.student-dashboard.profile.modals.experience-modal')
    @include('frontend.student-dashboard.profile.modals.add-education-modal')
@endsection
