@extends('frontend.instructor-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Zoom Live Setting') }}</h2>
    <div class="card"><div class="card-body"><form action="{{ route('instructor.zoom-setting.update') }}" method="POST">@csrf @method('PUT')
    <p class="text-muted">{{ __('Zoom API keys are stored in configuration — only the account email is shown here.') }}</p>
    <div class="mb-3"><label class="form-label">{{ __('Zoom Email') }}</label><input type="email" name="zoom_email" value="{{ old('zoom_email', $zoomSetting->email ?? '') }}" class="form-control"></div>
    <div class="mb-3"><label class="form-label">{{ __('API Key (public part only)') }}</label><input type="text" name="zoom_api_key" value="" class="form-control" placeholder="{{ __('Leave blank to keep the saved key') }}" autocomplete="off"></div>
    <button class="btn btn-primary">{{ __('Save Zoom Settings') }}</button></form></div></div>
@endsection
