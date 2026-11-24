@extends('frontend.instructor-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Jitsi Live Setting') }}</h2>
    <div class="card"><div class="card-body"><form action="{{ route('instructor.jitsi-setting.update') }}" method="POST">@csrf @method('PUT')
    <div class="mb-3"><label class="form-label">{{ __('Jitsi Domain') }}</label><input type="text" name="jitsi_domain" value="{{ old('jitsi_domain', $jitsiSetting->domain ?? 'meet.jit.si') }}" class="form-control"></div>
    <div class="mb-3"><label class="form-label">{{ __('App ID') }}</label><input type="text" name="jitsi_app_id" value="{{ old('jitsi_app_id', $jitsiSetting->app_id ?? '') }}" class="form-control"></div>
    <button class="btn btn-primary">{{ __('Save Jitsi Settings') }}</button></form></div></div>
@endsection
