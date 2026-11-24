{{--
    Blog list. $blogs (paginated), $blogCategories (optional sidebar).
--}}
@extends('frontend.layouts.master')
@section('meta_title', $seo_setting['blog_page']['seo_title'] ?? __('Blog') . ' | ' . setting('app_name', 'ATS Skill'))
@section('meta_description', $seo_setting['blog_page']['seo_description'] ?? '')

@section('contents')
    @include('frontend.partials.breadcrumb', ['title' => __('Blog'), 'links' => [['url' => route('home'), 'text' => __('Home')], ['url' => '', 'text' => __('Blog')]]])

    <section class="blog-area py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-xl-9 col-lg-8">
                    <div class="row g-4">
                        @forelse ($blogs ?? [] as $blog)
                            <div class="col-xl-4 col-md-6">
                                @include('frontend.partials.blog-card', ['blog' => $blog])
                            </div>
                        @empty
                            <div class="col-12"><p class="text-center py-5">{{ __('No Data Found') }}</p></div>
                        @endforelse
                    </div>
                    @if (method_exists($blogs ?? collect(), 'links'))
                        <div class="d-flex justify-content-center mt-4">{{ $blogs->withQueryString()->links() }}</div>
                    @endif
                </div>
                <div class="col-xl-3 col-lg-4">
                    <aside class="card p-3">
                        <h4 class="h6 fw-bold">{{ __('Categories') }}</h4>
                        <ul class="list-unstyled mb-0">
                            @foreach ($blogCategories ?? [] as $cat)
                                <li><a href="{{ route('blogs', ['category' => $cat->slug]) }}">{{ $cat->translation->title ?? $cat->slug }}</a></li>
                            @endforeach
                        </ul>
                    </aside>
                </div>
            </div>
        </div>
    </section>
@endsection
