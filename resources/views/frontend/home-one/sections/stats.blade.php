{{--
    9. Stats strip. $counter: counter_sections row
    (total_student_count, total_instructor_count, total_courses_count,
    total_awards_count).
--}}
<section class="fact__area">
    <div class="container">
        <div class="fact__inner-wrap">
            <div class="row g-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="fact__item">
                        <h2 class="count"><span>{{ number_format($counter->total_student_count ?? 0) }}</span>+</h2>
                        <p class="mb-0">{{ __('Active Students') }}</p>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="fact__item">
                        <h2 class="count"><span>{{ number_format($counter->total_instructor_count ?? 0) }}</span>+</h2>
                        <p class="mb-0">{{ __('Expert Instructors') }}</p>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="fact__item">
                        <h2 class="count"><span>{{ number_format($counter->total_courses_count ?? 0) }}</span>+</h2>
                        <p class="mb-0">{{ __('Total Courses') }}</p>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="fact__item">
                        <h2 class="count"><span>{{ number_format($counter->total_awards_count ?? 0) }}</span>+</h2>
                        <p class="mb-0">{{ __('Award Achieved') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
