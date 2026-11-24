{{--
    5. Expert Instructors (Swiper). $instructors: featured instructors
    (users with role instructor) with job_title, image, social links,
    avg_rating. $featuredInstructorSection (optional): title/sub_title/
    button_text/button_url from featured_instructor_translations.
--}}
<section class="instructor__area py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-7 col-lg-8">
                <div class="section__title text-center mb-5">
                    <h2 class="title">{!! $featuredInstructorSection->translation->title ?? __('Our Expert Instructors') !!}</h2>
                    @if ($featuredInstructorSection->translation->sub_title ?? null)
                        <p class="mt-3">{!! $featuredInstructorSection->translation->sub_title !!}</p>
                    @endif
                    <div class="tg-button-wrap mt-4">
                        <a href="{{ $featuredInstructorSection->button_url ?? route('all-instructors') }}" class="btn arrow-btn">
                            {{ $featuredInstructorSection->translation->button_text ?? __('See All Instructors') }} <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="instructor__item-wrap">
                    <div class="swiper instructor-active-three">
                        <div class="swiper-wrapper">
                            @forelse ($instructors ?? [] as $instructor)
                                <div class="swiper-slide">
                                    <div class="instructor__item">
                                        <div class="instructor__thumb">
                                            <a href="{{ route('instructor-details', ['id' => $instructor->id]) }}">
                                                <img src="{{ \App\Models\Media::resolveUrl($instructor->image ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ $instructor->name }}" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="instructor__content">
                                            <h3 class="title h5"><a href="{{ route('instructor-details', ['id' => $instructor->id]) }}">{{ $instructor->name }}</a></h3>
                                            <span class="designation">{{ $instructor->job_title ?? '' }}</span>
                                            <p class="avg-rating mt-2 mb-0"><i class="fas fa-star"></i> {{ number_format($instructor->avg_rating ?? 0, 1) }} {{ __('Ratings') }}</p>
                                            <div class="instructor__social">
                                                <ul class="list-wrap">
                                                    @if (!empty($instructor->facebook))<li><a href="{{ $instructor->facebook }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>@endif
                                                    @if (!empty($instructor->twitter))<li><a href="{{ $instructor->twitter }}" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a></li>@endif
                                                    @if (!empty($instructor->linkedin))<li><a href="{{ $instructor->linkedin }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>@endif
                                                    @if (!empty($instructor->github))<li><a href="{{ $instructor->github }}" target="_blank" rel="noopener" aria-label="GitHub"><i class="fab fa-github"></i></a></li>@endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="swiper-slide"><p class="text-center w-100">{{ __('No Instructor Found!') }}</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
