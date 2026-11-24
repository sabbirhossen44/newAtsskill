{{--
    8. Testimonials (Swiper). $testimonials: testimonials with translation
    (name, designation, comment) + image + rating.
--}}
<section class="testimonial__area py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill px-4 py-2 mb-3" style="background:#005bfe;font-size:1rem;border:2px solid gold;">{{ __('Our Testimonials') }}</span>
            <h2 class="fw-bold">{{ __('What Students Think and Say About Us') }}</h2>
            <p class="fs-5" style="color:#1fae0e;">{{ __('Genuine feedback from our students about their experiences with our expert teaching of ATS Skill') }}</p>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="testimonial__item-wrap">
                    <div class="swiper testimonial-swiper-active">
                        <div class="swiper-wrapper">
                            @forelse ($testimonials ?? [] as $testimonial)
                                <div class="swiper-slide">
                                    <div class="testimonial__item">
                                        <div class="testimonial__item-top">
                                            <div class="testimonial__author">
                                                <div class="testimonial__author-thumb">
                                                    <img src="{{ \App\Models\Media::resolveUrl($testimonial->image ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ $testimonial->translation->name ?? '' }}" loading="lazy">
                                                </div>
                                                <div class="testimonial__author-content">
                                                    <div class="rating">
                                                        @for ($i = 0; $i < (int) ($testimonial->rating ?? 0); $i++)<i class="fas fa-star"></i>@endfor
                                                    </div>
                                                    <h3 class="title h5 mb-0">{{ $testimonial->translation->name ?? '' }}</h3>
                                                    <small class="text-muted">{{ $testimonial->translation->designation ?? '' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="testimonial__content mt-3">
                                            <p class="mb-0">“ {{ $testimonial->translation->comment ?? '' }} ”</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="swiper-slide"><p class="text-center w-100">{{ __('No Testimonial Found!') }}</p></div>
                            @endforelse
                        </div>
                    </div>
                    <div class="testimonial__nav">
                        <button type="button" class="testimonial-button-prev" aria-label="{{ __('Previous') }}">←</button>
                        <button type="button" class="testimonial-button-next" aria-label="{{ __('Next') }}">→</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
