@extends('admin.master_layout')
@section('title')
    <title>Custom Code | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="card"><div class="card-body"><form action="{{ route('admin.update-custom-code') }}" method="POST">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label">{{ __('Custom CSS') }}</label><textarea name="custom_css" rows="6" class="form-control">{{ old('custom_css', $customCode->css ?? '') }}</textarea></div>
        <div class="mb-3"><label class="form-label">{{ __('Custom JS') }}</label><textarea name="custom_js" rows="6" class="form-control">{{ old('custom_js', $customCode->js ?? '') }}</textarea></div>
        <button class="btn btn-primary">{{ __('Save') }}</button></form></div></div>
@endsection
