<div class="modal fade" id="edit-experience-modal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">{{ __('Edit Experience') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('student.setting.experience.update', $item->id ?? 0) }}" method="POST">@csrf @method('PUT')
    <div class="modal-body"><div class="row g-3">
        <div class="col-12"><label class="form-label">{{ __('Title / Degree') }}</label><input type="text" name="title" class="form-control" required></div>
        <div class="col-12"><label class="form-label">{{ __('Institution / Company') }}</label><input type="text" name="institution" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('Start Date') }}</label><input type="date" name="start_date" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('End Date') }}</label><input type="date" name="end_date" class="form-control"></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button><button class="btn btn-primary">{{ __('Save') }}</button></div>
    </form></div></div></div>
