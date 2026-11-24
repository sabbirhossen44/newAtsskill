{{--
    7. Become Instructor / Become Student cards.
    $bannerSection: banner_sections row (instructor_image, student_image).
--}}
<section class="instructor__area-two py-5">
    <div class="container">
        <div class="instructor__item-wrap-two">
            <div class="row g-4">
                <div class="col-xl-6">
                    <div class="instructor__item-two">
                        <div class="instructor__thumb-two">
                            <img src="{{ \App\Models\Media::resolveUrl($bannerSection->instructor_image ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ __('Become an Instructor') }}" loading="lazy">
                        </div>
                        <div class="instructor__content-two">
                            <h3 class="title"><a href="{{ route('register') }}">{{ __('Become a Instructor') }}</a></h3>
                            <p>{{ __('Join our team to inspire students, share your knowledge, and shape the future') }}.</p>
                            <div class="tg-button-wrap">
                                <a href="{{ route('register') }}" class="btn arrow-btn">{{ __('Join Now') }} <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="instructor__item-two">
                        <div class="instructor__thumb-two">
                            <img src="{{ \App\Models\Media::resolveUrl($bannerSection->student_image ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ __('Become a Student') }}" loading="lazy">
                        </div>
                        <div class="instructor__content-two">
                            <h3 class="title"><a href="{{ route('register') }}">{{ __('Become a Student') }}</a></h3>
                            <p>{{ __('Unlock your potential by joining our vibrant learning community') }}.</p>
                            <div class="tg-button-wrap">
                                <a href="{{ route('register') }}" class="btn arrow-btn">{{ __('Join Now') }} <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
