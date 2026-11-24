{{--
    Instructor details. $instructor (user: image, bio, job_title, socials,
    courses, educations, experiences), $badges (optional, keyed badge groups).
--}}
@extends('frontend.layouts.master')
@section('meta_title', ($instructor->name ?? __('Instructor Details')) . ' | ' . setting('app_name', 'ATS Skill'))

@section('contents')
    @include('frontend.partials.breadcrumb', ['title' => __('Instructor Details'), 'links' => [['url' => route('home'), 'text' => __('Home')], ['url' => route('all-instructors'), 'text' => __('Instructors')], ['url' => '', 'text' => $instructor->name ?? '']]])

    <section class="instructor__details-area py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-xl-9">
                    <div class="instructor__details-wrap card p-3 p-md-4">
                        <div class="d-flex flex-column flex-md-row gap-4">
                            <img src="{{ \App\Models\Media::resolveUrl($instructor->image ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ $instructor->name }}" class="rounded" style="width:220px;height:260px;object-fit:cover;object-position:top;">
                            <div>
                                <h2 class="fw-bold mb-1">{{ $instructor->name }}</h2>
                                <p class="text-muted">{{ $instructor->job_title ?? '' }}</p>
                                <p class="avg-rating"><i class="fas fa-star"></i> {{ number_format($instructor->avg_rating ?? 0, 1) }} {{ __('Ratings') }} · {{ $instructor->courses_count ?? ($instructor->courses->count() ?? 0) }} {{ __('Courses') }} · {{ $instructor->total_students ?? 0 }} {{ __('Students') }}</p>
                                <div class="instructor__social">
                                    <ul class="list-wrap justify-content-start">
                                        @if (!empty($instructor->facebook))<li><a href="{{ $instructor->facebook }}" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a></li>@endif
                                        @if (!empty($instructor->twitter))<li><a href="{{ $instructor->twitter }}" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a></li>@endif
                                        @if (!empty($instructor->linkedin))<li><a href="{{ $instructor->linkedin }}" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a></li>@endif
                                        @if (!empty($instructor->website))<li><a href="{{ $instructor->website }}" target="_blank" rel="noopener"><i class="fas fa-globe"></i></a></li>@endif
                                        @if (!empty($instructor->github))<li><a href="{{ $instructor->github }}" target="_blank" rel="noopener"><i class="fab fa-github"></i></a></li>@endif
                                    </ul>
                                </div>
                                <form action="{{ route('quick-connect', ['id' => $instructor->id]) }}" method="POST" class="mt-3">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">{{ __('Quick Connect') }}</button>
                                </form>
                            </div>
                        </div>
                        @if ($instructor->bio ?? null)
                            <div class="mt-4"><h3 class="h5 fw-bold">{{ __('About') }}</h3><p>{!! $instructor->bio !!}</p></div>
                        @endif
                        @if (!empty($instructor->educations) && $instructor->educations->count())
                            <div class="mt-4"><h3 class="h5 fw-bold">{{ __('Education') }}</h3>
                                <ul>@foreach ($instructor->educations as $edu)<li>{{ $edu->degree }} — {{ $edu->organization }} ({{ $edu->start_date?->format('Y') }}–{{ $edu->current ? __('Present') : $edu->end_date?->format('Y') }})</li>@endforeach</ul>
                            </div>
                        @endif
                        @if (!empty($instructor->experiences) && $instructor->experiences->count())
                            <div class="mt-4"><h3 class="h5 fw-bold">{{ __('Experience') }}</h3>
                                <ul>@foreach ($instructor->experiences as $exp)<li>{{ $exp->position }} — {{ $exp->company }} ({{ $exp->start_date?->format('Y') }}–{{ $exp->current ? __('Present') : $exp->end_date?->format('Y') }})</li>@endforeach</ul>
                            </div>
                        @endif
                    </div>
                    <div class="mt-5">
                        <h3 class="h4 fw-bold mb-3">{{ __('Courses by') }} {{ $instructor->name }}</h3>
                        <div class="row g-4">
                            @forelse ($instructor->courses ?? [] as $course)
                                <div class="col-lg-4 col-md-6">@include('frontend.partials.course-card', ['course' => $course])</div>
                            @empty
                                <div class="col-12"><p class="text-muted">{{ __('No courses yet.') }}</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-xl-3">
                    <aside class="card p-3 position-sticky" style="top:90px;">
                        <h4 class="h6 fw-bold">{{ __('Contact Info') }}</h4>
                        <ul class="list-unstyled mb-0">
                            @if ($instructor->email)<li class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i>{{ $instructor->email }}</li>@endif
                            @if ($instructor->phone)<li class="mb-2"><i class="fas fa-phone-alt me-2 text-primary"></i>{{ $instructor->phone }}</li>@endif
                            @if ($instructor->address)<li><i class="fas fa-map-marker-alt me-2 text-primary"></i>{{ $instructor->address }}</li>@endif
                        </ul>
                    </aside>
                </div>
            </div>
        </div>
    </section>
@endsection
