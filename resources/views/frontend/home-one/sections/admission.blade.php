{{--
    10. Admission banner. Image fallbacks point at the placeholder until the
    admission artwork (images/admission/bg.jpg, images/admission/student.png)
    is re-supplied — the old uploads were missing from the zip.
--}}
<section class="admission-area py-5" style="background: linear-gradient(120deg,#0044cc,#005bfe);">
    <div class="container">
        <div class="row align-items-center justify-content-center g-4">
            <div class="col-12 col-lg-5 text-center">
                <div class="admission__img d-inline-block rounded overflow-hidden shadow" style="max-width:400px;">
                    <img src="{{ asset('images/admission/student.png') }}" alt="{{ __('Student') }}" class="img-fluid" onerror="this.src='{{ asset('images/placeholder.svg') }}'">
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="admission__content text-center text-lg-start mx-auto" style="max-width:550px;">
                    <h2 class="text-white fw-bold mb-3">{{ __('Admission is going on ATS Skill') }}</h2>
                    <p class="text-white mb-4">{{ __('Before admission please explore your ATS Skill course to get admission, ensure about your course & class schedule through call to office.') }}</p>
                    <div class="d-flex justify-content-center justify-content-lg-start flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="btn px-5 py-3 rounded-pill text-white fw-semibold" style="background:#1fae0e;border:2px solid #cda11f;">{{ __('Get Admission') }}</a>
                        <a href="{{ route('courses') }}" class="btn btn-outline-light px-5 py-3 rounded-pill fw-semibold" style="border:2px solid #cda11f;">{{ __('Explore Courses') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
