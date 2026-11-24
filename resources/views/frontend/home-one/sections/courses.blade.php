{{--
    3. Popular Courses. $courses: up to 8 approved/active courses.
    $featuredCourse (optional): featured_course_sections row; when set, its
    category tabs (category_one..four + *_ids) render as Bootstrap tabs.
    Card markup lives in frontend.partials.course-card.
--}}
<section class="courses-area section-pt-150 section-pb-90 py-5" style="background-color:#f4f7fb;">
    <div class="container">
        <div class="section__title-wrap">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <div class="section__title text-center mb-3">
                        <h2 class="title">{{ __('Popular Courses of ATS Skill') }}</h2>
                    </div>
                    <p class="text-center mx-auto mb-4" style="max-width:580px;font-size:1.05rem;">
                        {{ __('Explore our advanced-level skill development IT courses, unlock new skills and improve your future career with ATS Skill.') }}
                    </p>
                    @if (!empty($courseTabs))
                        <div class="courses__nav">
                            <ul class="nav nav-tabs justify-content-center border-0" id="courseTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all-tab-pane" type="button" role="tab">{{ __('All Courses') }}</button>
                                </li>
                                @foreach ($courseTabs as $tab)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pane-{{ $tab['id'] }}" type="button" role="tab">{{ $tab['name'] }}</button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="tab-content mt-4" id="courseTabContent">
            <div class="tab-pane fade show active" id="all-tab-pane" role="tabpanel">
                <div class="row g-4">
                    @forelse ($courses ?? [] as $course)
                        <div class="col-xxl-3 col-lg-4 col-md-6">
                            @include('frontend.partials.course-card', ['course' => $course])
                        </div>
                    @empty
                        <div class="col-12"><h6 class="text-center">{{ __('No Course Found!') }}</h6></div>
                    @endforelse
                </div>
            </div>
            @foreach ($courseTabs ?? [] as $tab)
                <div class="tab-pane fade" id="tab-pane-{{ $tab['id'] }}" role="tabpanel">
                    <div class="row g-4">
                        @forelse ($tab['courses'] ?? [] as $course)
                            <div class="col-xxl-3 col-lg-4 col-md-6">
                                @include('frontend.partials.course-card', ['course' => $course])
                            </div>
                        @empty
                            <div class="col-12"><h6 class="text-center">{{ __('No Course Found!') }}</h6></div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('courses') }}" class="btn arrow-btn">{{ __('View All Courses') }} <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
</section>
