{{--
    4. Newsletter. $newsletterSection: newsletter_sections row (image).
    Posts to the newsletter subscribe route (see README — route name to
    confirm with the routes agent: newsletter.subscribe).
--}}
<section class="newsletter__area">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-4 text-center">
                <div class="newsletter__img-wrap">
                    <img src="{{ \App\Models\Media::resolveUrl($newsletterSection->image ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ __('Newsletter') }}" class="img-fluid">
                </div>
            </div>
            <div class="col-12 col-lg-8">
                <div class="newsletter__content">
                    <h2 class="title"><b>{{ __('Want to stay informed about') }}</b><br><b>{{ __('ATS Skill new courses & study') }}?</b></h2>
                    <div class="newsletter__form mt-3">
                        <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter">
                            @csrf
                            <input type="email" name="email" required placeholder="{{ __('Type your email') }}" aria-label="{{ __('Email address') }}">
                            <button type="submit" class="btn">{{ __('Subscribe Now') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
