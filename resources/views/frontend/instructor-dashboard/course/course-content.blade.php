<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">{{ __('Course Content') }}</h5>
<button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSectionModal"><i class="fas fa-plus me-1"></i>{{ __('Add Section') }}</button></div>
<div class="accordion" id="contentAccordion">
@forelse ($course->chapters ?? [] as $chapter)
    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cc-{{ $chapter->id }}">{{ $chapter->title }}</button></h2>
    <div id="cc-{{ $chapter->id }}" class="accordion-collapse collapse" data-bs-parent="#contentAccordion"><div class="accordion-body">
        <div class="d-flex gap-2 mb-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#lessonCreateModal" data-chapter-id="{{ $chapter->id }}">{{ __('Add Lesson') }}</button>
            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#quizCreateModal" data-chapter-id="{{ $chapter->id }}">{{ __('Add Quiz') }}</button>
            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#documentCreateModal" data-chapter-id="{{ $chapter->id }}">{{ __('Add Document') }}</button>
            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#liveCreateModal" data-chapter-id="{{ $chapter->id }}">{{ __('Add Live Class') }}</button>
            <a href="{{ route('instructor.course-chapter.sorting.index', $course->id ?? 0) }}" class="btn btn-sm btn-outline-secondary">{{ __('Sort') }}</a>
            <form action="{{ route('instructor.course-chapter.destroy', $chapter->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this section?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">{{ __('Delete Section') }}</button></form>
        </div>
        <ul class="list-group">@foreach ($chapter->chapterItems ?? [] as $item)<li class="list-group-item d-flex justify-content-between"><span><i class="fas fa-{{ enum_value($item->type) == 'quiz' ? 'question-circle' : (enum_value($item->type) == 'document' ? 'file' : (enum_value($item->type) == 'live' ? 'video' : 'play-circle')) }} me-1"></i>{{ $item->lesson->title ?? $item->quiz->title ?? enum_value($item->type) }}</span>
        <span><form action="{{ route('instructor.course-chapter.lesson.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></span></li>@endforeach</ul>
    </div></div></div>
@empty<p class="text-muted">{{ __('No sections yet. Add a section to start building content.') }}</p>@endforelse
</div>
@include('frontend.instructor-dashboard.course.partials.add-new-section-modal')
@include('frontend.instructor-dashboard.course.partials.lesson-create-modal')
@include('frontend.instructor-dashboard.course.partials.quiz-create-modal')
@include('frontend.instructor-dashboard.course.partials.document-create-modal')
@include('frontend.instructor-dashboard.course.partials.live-create-modal')
