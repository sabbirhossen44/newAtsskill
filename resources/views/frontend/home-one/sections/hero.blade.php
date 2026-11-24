{{--
    1. Hero. $hero: hero_sections row + translation (title, sub_title,
    action_button_text/url, video_button_text/url, total_student,
    total_instructor) + banner_image. Falls back to the live headline.
--}}
@php
    $heroTitle = $hero->translation->title ?? null;
    $heroSub = $hero->translation->sub_title ?? __('Learn real, job-ready tech skills from expert mentors — and turn them into income.');
    $heroImg = \App\Models\Media::resolveUrl($hero->banner_image ?? null) ?? asset('images/placeholder.svg');
@endphp
<section class="ats-hero-section">
    <div class="container py-5 py-lg-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <div class="banner__content">
                    <span class="ats-discount-badge mb-3">{{ __('30% Discount') }}</span>
                    @if ($heroTitle)
                        <h1 class="title ats-hero-title mt-2">{!! $heroTitle !!}</h1>
                    @else
                        <h1 class="title ats-hero-title mt-2">
                            <span class="hero-line hero-line-dark">{{ __('Convert Your Own') }}</span>
                            <span class="hero-line hero-line-blue">{{ __('Tech Skills To Cash') }}</span>
                            <span class="hero-line hero-line-last">{{ __('In Just') }} <span class="hero-badge-pill">90 {{ __('Days') }}</span></span>
                        </h1>
                    @endif
                    <p class="mt-3 fs-5 text-secondary">{!! $heroSub !!}</p>
                    <div class="banner__btn-two d-flex flex-wrap gap-3 mt-4">
                        @if ($hero->translation->action_button_text ?? null)
                            <a href="{{ $hero->action_button_url ?? route('courses') }}" class="btn arrow-btn">{{ $hero->translation->action_button_text }}</a>
                        @else
                            <a href="{{ route('courses') }}" class="btn arrow-btn">{{ __('Explore Courses') }}</a>
                            <a href="{{ route('register') }}" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">{{ __('Get Admission') }}</a>
                        @endif
                        @if ($hero->translation->video_button_text ?? null)
                            <a href="{{ $hero->video_button_url ?? '#' }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold">{{ $hero->translation->video_button_text }}</a>
                        @endif
                    </div>
                    @if (($hero->translation->total_student ?? null) || ($hero->translation->total_instructor ?? null))
                        <div class="d-flex flex-wrap gap-4 mt-4 text-secondary">
                            @if ($hero->translation->total_student ?? null)
                                <span><strong class="text-dark">{{ $hero->translation->total_student }}</strong> {{ __('Enrolled Students') }}</span>
                            @endif
                            @if ($hero->translation->total_instructor ?? null)
                                <span><strong class="text-dark">{{ $hero->translation->total_instructor }}</strong> {{ __('Total Instructors') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-6">
                <div class="banner__images text-center">
                    <img src="{{ $heroImg }}" alt="{{ setting('app_name', 'ATS Skill') }}" class="main-img img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>
