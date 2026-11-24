<div class="card mb-3"><div class="card-body">
        <h5>{{ __('Address') }}</h5>
        <form action="{{ route('instructor.setting.address.update') }}" method="POST">@csrf @method('PUT')
        <div class="row g-3">
        <div class="col-md-6"><label class="form-label">{{ __('Country') }}</label><select name="country_id" class="form-select"><option value="">{{ __('Select') }}</option>@foreach ($countries ?? [] as $c)<option value="{{ $c->id }}" {{ ($user->country_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label">{{ __('City') }}</label><input type="text" name="city" value="{{ old('city', $user->city ?? '') }}" class="form-control"></div>
        <div class="col-12"><label class="form-label">{{ __('Address') }}</label><textarea name="address" rows="2" class="form-control">{{ old('address', $user->address ?? '') }}</textarea></div>
        </div><div class="mt-3"><button class="btn btn-primary">{{ __('Update Address') }}</button></div></form>
    </div></div>
