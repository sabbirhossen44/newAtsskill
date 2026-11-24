{{--
    12. Latest News Feed. $blogs: up to 4 blogs with show_homepage = 1
    (old variable was $featuredBlogs).
--}}
<section class="blog__post-area py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="section__title text-center mb-4">
                    <h2 class="title">{{ __('Latest News Feed of ATS Skill') }}</h2>
                </div>
            </div>
        </div>
        <div class="row g-4">
            @forelse ($blogs ?? [] as $blog)
                <div class="col-xxl-3 col-md-6 col-lg-4">
                    @include('frontend.partials.blog-card', ['blog' => $blog])
                </div>
            @empty
                <div class="col-12"><p class="text-center">{{ __('No Blog Found!') }}</p></div>
            @endforelse
        </div>
    </div>
</section>
