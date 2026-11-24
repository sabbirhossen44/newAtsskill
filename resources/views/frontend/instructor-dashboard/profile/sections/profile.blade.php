<div class="card mb-3"><div class="card-body">
        <h5>{{ __('Profile Information') }}</h5>
        <form action="{{ route('instructor.setting.profile.update') }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
        <div class="row g-3">
        <div class="col-md-6"><label class="form-label">{{ __('Name') }}</label><input type="text" name="name" value="{{ old('name', $user->name ?? auth()->user()->name) }}" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">{{ __('Email') }}</label><input type="email" name="email" value="{{ old('email', $user->email ?? auth()->user()->email) }}" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">{{ __('Phone') }}</label><input type="text" name="phone" value="{{ old('phone', $user->phone ?? auth()->user()->phone) }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('Gender') }}</label><select name="gender" class="form-select"><option value="male" {{ ($user->gender ?? '') == 'male' ? 'selected' : '' }}>{{ __('Male') }}</option><option value="female" {{ ($user->gender ?? '') == 'female' ? 'selected' : '' }}>{{ __('Female') }}</option><option value="other" {{ ($user->gender ?? '') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option></select></div>
        <div class="col-md-6"><label class="form-label">{{ __('Image') }}</label><input type="file" name="image" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('Cover') }}</label><input type="file" name="cover" class="form-control"></div>
        </div><div class="mt-3"><button class="btn btn-primary">{{ __('Update Profile') }}</button></div></form>
    </div></div>
