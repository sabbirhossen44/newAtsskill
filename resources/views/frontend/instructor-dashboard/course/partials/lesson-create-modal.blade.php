<div class="modal fade" id="lessonCreateModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">{{ __('Add Lesson') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('instructor.course-chapter.lesson.store') }}" method="POST" enctype="multipart/form-data">@csrf
    <input type="hidden" name="course_id" value="{{ $course->id ?? '' }}"><input type="hidden" name="chapter_id" value="{{ $chapter->id ?? '' }}">
    <div class="modal-body"><div class="mb-3"><label class="form-label">Lesson Title</label><input type="text" name="title" class="form-control"></div>
<div class="mb-3"><label class="form-label">Duration (minutes)</label><input type="number" name="duration" class="form-control"></div>
<div class="mb-3"><label class="form-label">Video URL / File</label><input type="text" name="file_path" class="form-control"></div>
<div class="mb-3"><label class="form-label">Storage</label><select name="storage" class="form-select"><option value="upload">Upload</option><option value="youtube">YouTube</option><option value="vimeo">Vimeo</option><option value="external_link">External Link</option><option value="google_drive">Google Drive</option><option value="iframe">iFrame</option></select></div>
</div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button><button class="btn btn-primary">{{ __('Save') }}</button></div>
    </form></div></div></div>
