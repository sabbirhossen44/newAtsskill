{{--
    Custom page (privacy policy, terms, refund policy, our services...).
    $page: custom_pages row + translation (name, content).
--}}
@extends('frontend.layouts.master')
@section('meta_title', ($page->translation->name ?? __('Page')) . ' | ' . setting('app_name', 'ATS Skill'))

@section('contents')
    @include('frontend.partials.breadcrumb', ['title' => $page->translation->name ?? __('Page'), 'links' => [['url' => route('home'), 'text' => __('Home')], ['url' => '', 'text' => $page->translation->name ?? '']]])

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card p-3 p-md-4">
                        <h1 class="h3 fw-bold mb-3">{{ $page->translation->name ?? '' }}</h1>
                        <div>{!! $page->translation->content ?? '' !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
