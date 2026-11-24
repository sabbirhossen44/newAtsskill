<div class="text-center py-4">
    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
    <h5>{{ __('Your course is ready for review') }}</h5>
    <p class="text-muted">{{ __('Submit the course and an admin will review it before it goes live.') }}</p>
    <div class="d-flex gap-2 justify-content-center">
        <button type="submit" name="submit_for_review" value="1" class="btn btn-success">{{ __('Submit for Review') }}</button>
        <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-secondary">{{ __('Save as Draft') }}</button>
    </div>
    <p class="mt-3"><a href="{{ route('instructor.course.delete-request.show', $course->id ?? 0) }}">{{ __('Request course deletion') }}</a></p>
</div>
