<div class="card mb-3"><div class="card-body">
        <h5>{{ __('Social Links') }}</h5>
        <form action="{{ route('instructor.setting.socials.update') }}" method="POST">@csrf @method('PUT')
        <div class="row g-3">
        @foreach (['facebook' => 'Facebook', 'twitter' => 'Twitter / X', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'github' => 'GitHub'] as $key => $label)
        <div class="col-md-6"><label class="form-label">{{ $label }}</label><input type="url" name="{{ $key }}" value="{{ old($key, $user->$key ?? '') }}" class="form-control"></div>
        @endforeach
        </div><div class="mt-3"><button class="btn btn-primary">{{ __('Update Socials') }}</button></div></form>
    </div></div>
