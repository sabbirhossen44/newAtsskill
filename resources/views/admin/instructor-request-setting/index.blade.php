@extends('admin.master_layout')
@section('title')
    <title>Instructor Request Settings | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <h1 class="h4 mb-3">{{ __('Instructor Request Settings') }}</h1>
    <div class="card"><div class="card-body"><form action="{{ route('admin.instructor-request-setting.update', $setting->id ?? 0) }}" method="POST">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label">{{ __('Description') }}</label><textarea name="description" rows="4" class="form-control">{{ old('description', $setting->translation->description ?? $setting->description ?? '') }}</textarea></div>
        <div class="mb-3 form-check"><input type="checkbox" name="status" value="active" class="form-check-input" {{ ($setting->status ?? '') == 'active' ? 'checked' : '' }}><label class="form-check-label">{{ __('Enable instructor requests') }}</label></div>
        <button class="btn btn-primary">{{ __('Save') }}</button></form></div></div>
@endsection
