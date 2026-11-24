{{--
    All instructors. $instructors (paginated users with role instructor).
--}}
@extends('frontend.layouts.master')
@section('meta_title', __('All Instructors') . ' | ' . setting('app_name', 'ATS Skill'))

@section('contents')
    @include('frontend.partials.breadcrumb', ['title' => __('All Instructors'), 'links' => [['url' => route('home'), 'text' => __('Home')], ['url' => '', 'text' => __('Instructors')]]])

    <section class="instructor__area py-5">
        <div class="container">
            <div class="row g-4">
                @forelse ($instructors ?? [] as $instructor)
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="instructor__item">
                            <div class="instructor__thumb">
                                <a href="{{ route('instructor-details', ['id' => $instructor->id]) }}">
                                    <img src="{{ \App\Models\Media::resolveUrl($instructor->image ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ $instructor->name }}" loading="lazy">
                                </a>
                            </div>
                            <div class="instructor__content">
                                <h3 class="title h5"><a href="{{ route('instructor-details', ['id' => $instructor->id]) }}">{{ $instructor->name }}</a></h3>
                                <span class="designation">{{ $instructor->job_title ?? '' }}</span>
                                <p class="avg-rating mt-2 mb-0"><i class="fas fa-star"></i> {{ number_format($instructor->avg_rating ?? 0, 1) }} {{ __('Ratings') }} · {{ $instructor->courses_count ?? 0 }} {{ __('Courses') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12"><p class="text-center py-5">{{ __('No Instructor Found!') }}</p></div>
                @endforelse
            </div>
            @if (method_exists($instructors ?? collect(), 'links'))
                <div class="d-flex justify-content-center mt-4">{{ $instructors->withQueryString()->links() }}</div>
            @endif
        </div>
    </section>
@endsection
