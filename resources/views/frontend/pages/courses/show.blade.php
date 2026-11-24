{{--
    Course details. $course with category, instructor, chapters (each with
    items/lessons), reviews, enrollments_count, avg_rating; $relatedCourses
    (optional). Price in ৳. Enroll uses the add-to-cart flow; Buy Now goes to
    route('buy-now', $course->slug).
--}}
@extends('frontend.layouts.master')
@section('meta_title', ($course->title ?? __('Course Details')) . ' | ' . setting('app_name', 'ATS Skill'))
@push('custom_meta')
    <meta property="og:title" content="{{ $course->title ?? '' }}">
    <meta property="og:description" content="{{ $course->seo_description ?? '' }}">
    <meta property="og:image" content="{{ \App\Models\Media::resolveUrl($course->thumbnail ?? null) ?? asset('images/placeholder.svg') }}">
    <meta property="og:type" content="website">
@endpush

@section('contents')
    @include('frontend.partials.breadcrumb', ['title' => __('Course Details'), 'links' => [['url' => route('home'), 'text' => __('Home')], ['url' => route('courses'), 'text' => __('Courses')], ['url' => '', 'text' => $course->title ?? '']]])

    <section class="courses__details-area py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-xl-9 col-lg-8">
                    <div class="courses__details-thumb position-relative rounded overflow-hidden">
                        <img class="w-100" src="{{ \App\Models\Media::resolveUrl($course->thumbnail ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ $course->title }}">
                        @if ($course->demo_video_source ?? null)
                            <a href="{{ $course->demo_video_source }}" class="position-absolute top-50 start-50 translate-middle btn btn-light rounded-circle p-3" aria-label="{{ __('Play demo video') }}"><i class="fas fa-play"></i></a>
                        @endif
                    </div>
                    <div class="courses__details-content mt-4">
                        <ul class="courses__item-meta list-wrap">
                            <li class="courses__item-tag"><a href="{{ route('courses', ['category' => $course->category->id ?? null]) }}">{{ $course->category->translation->name ?? '' }}</a></li>
                            <li class="avg-rating"><i class="fas fa-star"></i> {{ number_format($course->avg_rating ?? 0, 1) }} {{ __('Reviews') }}</li>
                        </ul>
                        <h2 class="title">{{ $course->title }}</h2>
                        <ul class="list-inline text-muted">
                            <li class="list-inline-item"><i class="far fa-user me-1"></i>{{ __('Mentor:') }} <a href="{{ route('instructor-details', ['id' => $course->instructor->id ?? 0]) }}">{{ $course->instructor->name ?? '' }}</a></li>
                            <li class="list-inline-item"><i class="far fa-calendar-alt me-1"></i>{{ $course->created_at?->format('d M, Y') }}</li>
                            <li class="list-inline-item"><i class="fas fa-users me-1"></i>{{ $course->enrollments_count ?? 0 }} {{ __('Students') }}</li>
                            @if ($course->duration ?? null)<li class="list-inline-item"><i class="far fa-clock me-1"></i>{{ $course->duration }} {{ __('days') }}</li>@endif
                        </ul>
                        <div class="mt-3">{!! $course->description ?? '' !!}</div>
                    </div>

                    @if (!empty($course->chapters) && $course->chapters->count())
                        <div class="mt-5">
                            <h3 class="h4 fw-bold mb-3">{{ __('Course Content') }}</h3>
                            <div class="accordion" id="courseChapters">
                                @foreach ($course->chapters as $chapter)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="ch-heading-{{ $chapter->id }}">
                                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#ch-{{ $chapter->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                                {{ $chapter->title }}
                                            </button>
                                        </h2>
                                        <div id="ch-{{ $chapter->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#courseChapters">
                                            <div class="accordion-body">
                                                <ul class="list-unstyled mb-0">
                                                    @foreach ($chapter->items ?? $chapter->lessons ?? [] as $item)
                                                        <li class="d-flex justify-content-between py-1 border-bottom">
                                                            <span><i class="far fa-play-circle me-2 text-primary"></i>{{ $item->title ?? $item->lesson->title ?? '' }}</span>
                                                            <span class="text-muted small">{{ $item->duration ?? $item->lesson->duration ?? '' }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-5">
                        <h3 class="h4 fw-bold mb-3">{{ __('Instructor') }}</h3>
                        <div class="card p-3">
                            <div class="d-flex gap-3 align-items-center">
                                <img src="{{ \App\Models\Media::resolveUrl($course->instructor->image ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ $course->instructor->name ?? '' }}" class="rounded-circle" style="width:72px;height:72px;object-fit:cover;">
                                <div>
                                    <h5 class="mb-0"><a href="{{ route('instructor-details', ['id' => $course->instructor->id ?? 0]) }}">{{ $course->instructor->name ?? '' }}</a></h5>
                                    <small class="text-muted">{{ $course->instructor->job_title ?? '' }}</small>
                                    <p class="mb-0 small">{{ \Illuminate\Support\Str::limit($course->instructor->short_bio ?? $course->instructor->bio ?? '', 160) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (!empty($course->reviews))
                        <div class="mt-5">
                            <h3 class="h4 fw-bold mb-3">{{ __('Reviews') }}</h3>
                            @forelse ($course->reviews as $review)
                                <div class="card p-3 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $review->user->name ?? __('Student') }}</strong>
                                        <span class="avg-rating">@for ($i = 0; $i < (int) $review->rating; $i++)<i class="fas fa-star"></i>@endfor</span>
                                    </div>
                                    <p class="mb-0">{{ $review->review }}</p>
                                </div>
                            @empty
                                <p class="text-muted">{{ __('No reviews yet.') }}</p>
                            @endforelse
                        </div>
                    @endif
                </div>

                <div class="col-xl-3 col-lg-4">
                    <div class="courses__details-sidebar card p-3 position-sticky" style="top:90px;">
                        <div class="courses__cost-wrap mb-3">
                            <span class="text-muted">{{ __('This Course Fee') }}:</span>
                            @if (($course->price ?? 0) == 0)
                                <h2 class="title text-primary">{{ __('Free') }}</h2>
                            @elseif (($course->discount ?? 0) > 0)
                                <h2 class="title text-primary">৳{{ number_format($course->discount, 0) }} <del class="text-muted fs-6">৳{{ number_format($course->price, 0) }}</del></h2>
                            @else
                                <h2 class="title text-primary">৳{{ number_format($course->price, 0) }}</h2>
                            @endif
                        </div>
                        <div class="d-grid gap-2">
                            @if (in_array($course->id, session('enrollments', [])))
                                <a href="{{ route('student.enrolled-courses') }}" class="btn btn-success">{{ __('Go to My Courses') }}</a>
                            @else
                                <a href="javascript:;" class="btn btn-primary add-to-cart" data-id="{{ $course->id }}">{{ __('Add to Cart') }}</a>
                                <a href="{{ route('buy-now', $course->slug) }}" class="btn btn-success">{{ __('Buy Now') }}</a>
                            @endif
                        </div>
                        <div class="courses__information-wrap mt-4">
                            <h5 class="title h6 fw-bold">{{ __('Course includes') }}:</h5>
                            <ul class="list-unstyled mb-0">
                                @if ($course->certificate)<li><i class="fas fa-award me-2 text-primary"></i>{{ __('Certificate of completion') }}</li>@endif
                                @if ($course->downloadable)<li><i class="fas fa-download me-2 text-primary"></i>{{ __('Downloadable resources') }}</li>@endif
                                @if ($course->qna)<li><i class="fas fa-question-circle me-2 text-primary"></i>{{ __('Q&A support') }}</li>@endif
                                <li><i class="fas fa-infinity me-2 text-primary"></i>{{ __('Full lifetime access') }}</li>
                                <li><i class="fas fa-mobile-alt me-2 text-primary"></i>{{ __('Access on mobile and desktop') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            @if (!empty($relatedCourses) && $relatedCourses->count())
                <div class="mt-5">
                    <h3 class="h4 fw-bold mb-3">{{ __('Related Courses') }}</h3>
                    <div class="row g-4">
                        @foreach ($relatedCourses as $related)
                            <div class="col-xl-3 col-lg-4 col-md-6">@include('frontend.partials.course-card', ['course' => $related])</div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
