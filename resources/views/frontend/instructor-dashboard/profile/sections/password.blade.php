<div class="card mb-3"><div class="card-body">
        <h5>{{ __('Change Password') }}</h5>
        <form action="{{ route('instructor.setting.password.update') }}" method="POST">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label">{{ __('Current Password') }}</label><input type="password" name="current_password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">{{ __('New Password') }}</label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">{{ __('Confirm Password') }}</label><input type="password" name="password_confirmation" class="form-control" required></div>
        <button class="btn btn-primary">{{ __('Update Password') }}</button></form>
    </div></div>
