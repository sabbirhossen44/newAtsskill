{{--
    Course card (shared by homepage, course list, instructor page).
    Expects a single $course with: id, slug, title, thumbnail, price, discount,
    capacity, category?->translation?->name, instructor?->name, avg_rating,
    enrollments_count. Price is rendered in ৳ (BDT).
--}}
@php
    $thumb = \App\Models\Media::resolveUrl($course->thumbnail ?? null) ?? asset('images/placeholder.svg');
    $finalPrice = ($course->price > 0 && ($course->discount ?? 0) > 0) ? $course->discount : $course->price;
    $isEnrolled = in_array($course->id, $enrolledCourseIds ?? session('enrollments', []));
    $isFull = ($course->capacity ?? null) !== null && ($course->enrollments_count ?? 0) >= $course->capacity;
@endphp
<div class="courses__item h-100">
    <div class="courses__item-thumb">
        <a href="{{ route('course.show', $course->slug) }}">
            <img src="{{ $thumb }}" alt="{{ $course->title }}" loading="lazy">
        </a>
    </div>
    <div class="courses__item-content">
        <ul class="courses__item-meta list-wrap">
            <li class="courses__item-tag">
                <a href="{{ route('courses', ['category' => $course->category->id ?? null]) }}">{{ $course->category->translation->name ?? $course->category->name ?? __('Course') }}</a>
            </li>
            <li class="avg-rating"><i class="fas fa-star"></i> {{ number_format($course->avg_rating ?? 0, 1) }}</li>
        </ul>
        <h5 class="title"><a href="{{ route('course.show', $course->slug) }}">{{ \Illuminate\Support\Str::limit($course->title, 50) }}</a></h5>
        <p class="author">{{ __('Mentor:') }}
            <a href="{{ route('instructor-details', ['id' => $course->instructor->id ?? 0, 'slug' => \Illuminate\Support\Str::slug($course->instructor->name ?? '')]) }}">{{ $course->instructor->name ?? __('ATS Skill') }}</a>
        </p>
        <div class="courses__item-bottom">
            <div class="button">
                @if ($isEnrolled)
                    <a href="{{ route('student.enrolled-courses') }}" class="already-enrolled-btn"><span class="text">{{ __('Enrolled') }}</span> <i class="fas fa-arrow-right"></i></a>
                @elseif ($isFull)
                    <a href="javascript:;" class="text-muted"><span class="text">{{ __('Booked') }}</span></a>
                @else
                    <a href="javascript:;" class="add-to-cart" data-id="{{ $course->id }}" data-redirect="true"><span class="text">{{ __('Enroll Now') }}</span> <i class="fas fa-arrow-right"></i></a>
                @endif
            </div>
            @if (($course->price ?? 0) == 0)
                <h5 class="price">{{ __('Free') }}</h5>
            @else
                <h5 class="price">৳{{ number_format($finalPrice, 0) }}
                    @if (($course->discount ?? 0) > 0)<del>৳{{ number_format($course->price, 0) }}</del>@endif
                </h5>
            @endif
        </div>
    </div>
</div>
