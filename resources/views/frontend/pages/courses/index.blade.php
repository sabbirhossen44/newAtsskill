{{--
    Courses listing. $courses (paginated, with category/instructor/avg_rating/
    enrollments_count), $categories (top-level, with translation), $levels,
    $languages (optional filters). Filters submit via GET to route('courses');
    the old AJAX fetch-courses endpoint is not required for the first version.
--}}
@extends('frontend.layouts.master')
@section('meta_title', $seo_setting['course_page']['seo_title'] ?? __('Courses') . ' | ' . setting('app_name', 'ATS Skill'))
@section('meta_description', $seo_setting['course_page']['seo_description'] ?? '')

@section('contents')
    @include('frontend.partials.breadcrumb', ['title' => __('Courses'), 'links' => [['url' => route('home'), 'text' => __('Home')], ['url' => '', 'text' => __('Courses')]]])

    <section class="all-courses-area py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-xl-3 col-lg-4">
                    <button class="btn btn-outline-primary w-100 d-lg-none mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#courseFilters">{{ __('Filter') }}</button>
                    <div class="collapse d-lg-block" id="courseFilters">
                        <aside class="courses__sidebar card p-3">
                            <form action="{{ route('courses') }}" method="GET" id="courseFilterForm">
                                <div class="mb-4">
                                    <div class="input-group">
                                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ __('Search Courses') }}">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                                <div class="courses-widget mb-4">
                                    <h4 class="widget-title h6 fw-bold">{{ __('Categories') }}</h4>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="main_category" value="" id="cat_all" {{ ! request('main_category') ? 'checked' : '' }} onchange="this.form.submit()">
                                        <label class="form-check-label" for="cat_all">{{ __('All Categories') }}</label>
                                    </div>
                                    @foreach ($categories ?? [] as $category)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="main_category" value="{{ $category->slug }}" id="cat_{{ $category->id }}" {{ request('main_category') === $category->slug ? 'checked' : '' }} onchange="this.form.submit()">
                                            <label class="form-check-label" for="cat_{{ $category->id }}">{{ $category->translation->name ?? '' }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @if (!empty($levels))
                                    <div class="courses-widget mb-4">
                                        <h4 class="widget-title h6 fw-bold">{{ __('Level') }}</h4>
                                        @foreach ($levels as $level)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="levels[]" value="{{ $level->id }}" id="lvl_{{ $level->id }}" {{ in_array($level->id, (array) request('levels', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="lvl_{{ $level->id }}">{{ $level->translation->name ?? '' }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="courses-widget mb-3">
                                    <h4 class="widget-title h6 fw-bold">{{ __('Price') }}</h4>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="price" value="" id="price_all" {{ ! request('price') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="price_all">{{ __('All') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="price" value="free" id="price_free" {{ request('price') === 'free' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="price_free">{{ __('Free') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="price" value="paid" id="price_paid" {{ request('price') === 'paid' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="price_paid">{{ __('Paid') }}</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">{{ __('Apply Filters') }}</button>
                                <a href="{{ route('courses') }}" class="btn btn-link w-100">{{ __('Clear All') }}</a>
                            </form>
                        </aside>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="mb-0 text-muted">{{ __('Showing') }} {{ method_exists($courses ?? collect(), 'firstItem') ? $courses->firstItem() : 1 }}–{{ method_exists($courses ?? collect(), 'lastItem') ? $courses->lastItem() : count($courses ?? []) }} {{ __('of') }} {{ method_exists($courses ?? collect(), 'total') ? $courses->total() : count($courses ?? []) }} {{ __('courses') }}</p>
                        <form action="{{ route('courses') }}" method="GET" class="d-flex gap-2">
                            @foreach (request()->except('sort') as $k => $v)
                                @if (is_scalar($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
                            @endforeach
                            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('Sort') }}">
                                <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>{{ __('Newest') }}</option>
                                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>{{ __('Price: Low to High') }}</option>
                                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>{{ __('Price: High to Low') }}</option>
                                <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>{{ __('Top Rated') }}</option>
                            </select>
                        </form>
                    </div>
                    <div class="row g-4">
                        @forelse ($courses ?? [] as $course)
                            <div class="col-xl-4 col-md-6">
                                @include('frontend.partials.course-card', ['course' => $course])
                            </div>
                        @empty
                            <div class="col-12"><h6 class="text-center py-5">{{ __('No Course Found!') }}</h6></div>
                        @endforelse
                    </div>
                    @if (method_exists($courses ?? collect(), 'links'))
                        <div class="d-flex justify-content-center mt-4">{{ $courses->withQueryString()->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
