@extends('frontend.layouts.master')
@section('meta_title', ($course->title ?? __('Learning')) . ' | ' . setting('app_name', 'ATS Skill'))
@section('contents')
    @yield('learning-contents')
@endsection
