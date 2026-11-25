@extends('frontend.pages.learning-player.master')
@section('learning-contents')
<section class="py-4"><div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-angle-left me-1"></i>{{ __('Go back to dashboard') }}</a>
        <p class="mb-0 text-muted">{{ __('Your Progress') }}: {{ $courseLectureCompletedByUser ?? 0 }} {{ __('of') }} {{ $courseLectureCount ?? 0 }} ({{ number_format($courseCompletedPercent ?? 0) }}%)</p>
    </div>
    <div class="progress mb-3" style="height:10px;"><div class="progress-bar" style="width: {{ $courseCompletedPercent ?? 0 }}%"></div></div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card"><div class="card-body p-0 overflow-hidden rounded">
                <div id="lessonPlayer" class="ratio ratio-16x9 bg-dark d-flex align-items-center justify-content-center">
                    @include('frontend.pages.learning-player.partials.lesson-player', ['lesson' => $currentLesson ?? null, 'course' => $course ?? null])
                </div>
            </div></div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                @isset($currentLesson)
                <form action="{{ route('student.make-lesson-complete') }}" method="POST">@csrf
                    <input type="hidden" name="lesson_id" value="{{ $currentLesson->id }}"><input type="hidden" name="course_id" value="{{ $course->id ?? '' }}">
                    <button class="btn btn-success"><i class="fas fa-check me-1"></i>{{ __('Mark as Complete') }}</button></form>
                <a href="{{ route('student.download-resource', $currentLesson->id) }}" class="btn btn-outline-primary"><i class="fas fa-download me-1"></i>{{ __('Download Resources') }}</a>
                @endisset
                @isset($course)<a href="{{ route('student.download-certificate', $course->id) }}" class="btn btn-outline-warning"><i class="fas fa-certificate me-1"></i>{{ __('Download Certificate') }}</a>@endisset
            </div>
            @include('frontend.pages.learning-player.bottom-panel')
        </div>
        <div class="col-lg-4">
            <div class="card"><div class="card-header"><strong>{{ __('Course Content') }}</strong></div>
            <div class="accordion accordion-flush" id="learningAccordion">
            @foreach ($course->chapters ?? [] as $chapter)
                <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button {{ ($currentProgress->chapter_id ?? null) == $chapter->id ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#ch-{{ $chapter->id }}">{{ $chapter->title }}</button></h2>
                <div id="ch-{{ $chapter->id }}" class="accordion-collapse collapse {{ ($currentProgress->chapter_id ?? null) == $chapter->id ? 'show' : '' }}" data-bs-parent="#learningAccordion"><div class="accordion-body p-0"><ul class="list-group list-group-flush">
                @foreach ($chapter->chapterItems ?? [] as $chapterItem)
                    @php $lesson = $chapterItem->lesson ?? null; @endphp
                    @if (enum_value($chapterItem->type) == 'lesson' || enum_value($chapterItem->type) == 'live')
                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $lesson && ($currentProgress->lesson_id ?? null) == $lesson->id ? 'active' : '' }}">
                        <a href="{{ route('student.learning.index', $course->slug ?? $course->id) }}?lesson={{ $lesson->id ?? '' }}" class="text-decoration-none {{ $lesson && ($currentProgress->lesson_id ?? null) == $lesson->id ? 'text-white' : '' }}">
                        <i class="fas {{ enum_value($chapterItem->type) == 'live' ? 'fa-video' : 'fa-play-circle' }} me-1"></i>{{ $lesson->title ?? '' }}</a>
                        <span class="d-flex align-items-center gap-2"><small>{{ $lesson->duration ?? '' }}</small>
                        <input type="checkbox" class="form-check-input lesson-completed-checkbox" data-lesson-id="{{ $lesson->id ?? '' }}" {{ $lesson && in_array($lesson->id, $alreadyWatchedLectures ?? []) ? 'checked' : '' }}></span></li>
                    @elseif (enum_value($chapterItem->type) == 'quiz')
                    <li class="list-group-item"><a href="{{ route('student.quiz.index', $chapterItem->quiz->id ?? $chapterItem->id) }}"><i class="fas fa-question-circle me-1"></i>{{ $chapterItem->quiz->title ?? __('Quiz') }}</a></li>
                    @elseif (enum_value($chapterItem->type) == 'document')
                    <li class="list-group-item"><a href="{{ route('student.download-resource', $chapterItem->id) }}"><i class="fas fa-file me-1"></i>{{ $chapterItem->lesson->title ?? __('Document') }}</a></li>
                    @endif
                @endforeach
                </ul></div></div></div>
            @endforeach
            </div></div>
        </div>
    </div>
</div></section>
@endsection
