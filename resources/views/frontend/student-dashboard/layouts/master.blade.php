{{--
    Student dashboard shell. Mirrors the old student-dashboard/layouts/master:
    cover + avatar header, sidebar left, @yield('dashboard-contents') right.
--}}
@extends('frontend.layouts.master')
@section('meta_title', __('Student Dashboard') . ' | ' . setting('app_name', 'ATS Skill'))

@section('contents')
    <section class="dashboard__area py-5">
        <div class="container">
            <div class="dashboard__top-wrap mb-4">
                <div class="dashboard__top-bg" style="@if(auth()->user()->cover) background-image:url('{{ \App\Models\Media::resolveUrl(auth()->user()->cover) }}'); @endif"></div>
                <div class="dashboard__instructor-info px-3 px-md-4">
                    <div class="d-flex flex-column flex-md-row gap-3 align-items-md-end">
                        <div class="thumb">
                            <img src="{{ \App\Models\Media::resolveUrl(auth()->user()->image ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ auth()->user()->name }}">
                        </div>
                        <div class="pb-md-2">
                            <h4 class="title mb-1">{{ auth()->user()->name }}</h4>
                            <ul class="list-unstyled mb-0 text-muted">
                                <li><i class="fas fa-envelope me-2"></i>{{ auth()->user()->email }}</li>
                                @if (auth()->user()->phone)<li><i class="fas fa-phone-alt me-2"></i>{{ auth()->user()->phone }}</li>@endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3">
                    @include('frontend.student-dashboard.layouts.sidebar')
                </div>
                <div class="col-lg-9">
                    @yield('dashboard-contents')
                </div>
            </div>
        </div>
    </section>
@endsection
