{{--
    About Us. $about: about_sections row + translation (title, description,
    button_text) + image/image_two/image_three/video_url/year_experience.
    $teamMembers and $counter are optional extras.
--}}
@extends('frontend.layouts.master')
@section('meta_title', $seo_setting['about_page']['seo_title'] ?? __('About Us') . ' | ' . setting('app_name', 'ATS Skill'))
@section('meta_description', $seo_setting['about_page']['seo_description'] ?? '')

@section('contents')
    @include('frontend.partials.breadcrumb', ['title' => __('About Us'), 'links' => [['url' => route('home'), 'text' => __('Home')], ['url' => '', 'text' => __('About Us')]]])

    <section class="about-area py-5">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <div class="position-relative">
                        <img src="{{ \App\Models\Media::resolveUrl($about->image ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ __('About ATS Skill') }}" class="img-fluid rounded shadow">
                        @if ($about->year_experience ?? null)
                            <span class="badge bg-primary position-absolute bottom-0 start-0 m-3 p-3 fs-6">{{ $about->year_experience }} {{ __('Years Experience') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6">
                    <h2 class="fw-bold">{{ $about->translation->title ?? __('About ATS Skill') }}</h2>
                    <div class="mt-3">{!! $about->translation->description ?? __('ATS Skill is a skill-development platform from Munshiganj, Bangladesh. Our expert mentors teach practical, job-ready tech skills — and help students turn those skills into income.') !!}</div>
                    @if ($about->translation->button_text ?? null)
                        <a href="{{ $about->button_url ?? route('courses') }}" class="btn arrow-btn mt-3">{{ $about->translation->button_text }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if (!empty($counter))
        @include('frontend.home-one.sections.stats')
    @endif

    @if (!empty($teamMembers))
        @include('frontend.home-one.sections.team')
    @endif
@endsection
