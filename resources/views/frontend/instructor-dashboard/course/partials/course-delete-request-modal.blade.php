<div class="modal fade" id="course-delete-request-modal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">{{ __('Request Course Deletion') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('instructor.course.send-delete-request') }}" method="POST" enctype="multipart/form-data">@csrf
    <input type="hidden" name="course_id" value="{{ $course->id ?? '' }}"><input type="hidden" name="chapter_id" value="{{ $chapter->id ?? '' }}">
    <div class="modal-body"><div class="mb-3"><label class="form-label">Reason</label><input type="text" name="reason" class="form-control"></div>
</div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button><button class="btn btn-primary">{{ __('Save') }}</button></div>
    </form></div></div></div>
