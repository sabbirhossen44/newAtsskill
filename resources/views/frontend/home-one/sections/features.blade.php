{{--
    6. "Start Your Learning Journey Today!" — 4 tiles.
    $ourFeatures: our_features_sections row + translation
    (title_one..four / sub_title_one..four) + image_one..four.
--}}
<section class="features__area">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6">
                <div class="section__title white-title text-center mb-5">
                    <h2 class="title">{{ __('Start your Learning Journey Today!') }}</h2>
                    <p>{{ __('Discover a World of Knowledge and Skills at Your Fingertips – Unlock Your Potential and Achieve Your Dreams with Our Comprehensive Learning Resources!') }}</p>
                </div>
            </div>
        </div>
        <div class="row justify-content-center g-4">
            @foreach ([['img' => $ourFeatures->image_one ?? null, 'title' => $ourFeatures->translation->title_one ?? null, 'sub' => $ourFeatures->translation->sub_title_one ?? null], ['img' => $ourFeatures->image_two ?? null, 'title' => $ourFeatures->translation->title_two ?? null, 'sub' => $ourFeatures->translation->sub_title_two ?? null], ['img' => $ourFeatures->image_three ?? null, 'title' => $ourFeatures->translation->title_three ?? null, 'sub' => $ourFeatures->translation->sub_title_three ?? null], ['img' => $ourFeatures->image_four ?? null, 'title' => $ourFeatures->translation->title_four ?? null, 'sub' => $ourFeatures->translation->sub_title_four ?? null]] as $feature)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="features__item">
                        <div class="features__icon">
                            <img src="{{ \App\Models\Media::resolveUrl($feature['img']) ?? asset('images/placeholder.svg') }}" alt="" class="custom-round-icon" loading="lazy">
                        </div>
                        <div class="features__content">
                            <h4 class="title">{{ $feature['title'] ?? '' }}</h4>
                            <p class="mb-0">{{ $feature['sub'] ?? '' }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
