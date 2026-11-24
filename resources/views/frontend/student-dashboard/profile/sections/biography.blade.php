<div class="card mb-3"><div class="card-body">
        <h5>{{ __('Biography') }}</h5>
        <form action="{{ route('student.setting.bio.update') }}" method="POST">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label">{{ __('Designation') }}</label><input type="text" name="designation" value="{{ old('designation', $user->designation ?? '') }}" class="form-control"></div>
        <div class="mb-3"><label class="form-label">{{ __('Biography') }}</label><textarea name="bio" rows="4" class="form-control">{{ old('bio', $user->bio ?? '') }}</textarea></div>
        <button class="btn btn-primary">{{ __('Update Bio') }}</button></form>
    </div></div>
