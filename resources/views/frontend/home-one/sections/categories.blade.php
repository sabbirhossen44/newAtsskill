{{--
    2. Top Category carousel (Swiper). $categories: top-level course_categories
    with translation + courses count. Old variable was $trendingCategories.
--}}
<section class="courses-area grey-bg py-5" style="background-color:#f4f7fb;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-7">
                <div class="section__title text-center mb-4">
                    <h2 class="title">{{ __('Top Category of ATS Skill') }}</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="categories__wrap">
                    <div class="swiper categories-active">
                        <div class="swiper-wrapper">
                            @forelse ($categories ?? [] as $category)
                                <div class="swiper-slide">
                                    <div class="categories__item">
                                        <a href="{{ route('courses', ['main_category' => $category->slug]) }}">
                                            <div class="icon">
                                                <img src="{{ \App\Models\Media::resolveUrl($category->icon ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ $category->translation->name ?? '' }}" loading="lazy">
                                            </div>
                                            <span class="name">{{ $category->translation->name ?? $category->name ?? '' }}</span>
                                            <span class="courses">({{ $category->courses_count ?? $category->subCategories->sum('courses_count') ?? 0 }})</span>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="swiper-slide"><p class="text-center w-100">{{ __('No Category Found!') }}</p></div>
                            @endforelse
                        </div>
                    </div>
                    <div class="categories__nav">
                        <button type="button" class="categories-button-prev" aria-label="{{ __('Previous') }}">←</button>
                        <button type="button" class="categories-button-next" aria-label="{{ __('Next') }}">→</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
