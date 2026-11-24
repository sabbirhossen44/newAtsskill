@extends('admin.master_layout')
@section('title')
    <title>General Settings | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="row g-3"><div class="col-lg-3">@include('admin.settings.sidebar')</div><div class="col-lg-9"><div class="card"><div class="card-body">
        <form action="{{ route('admin.update-general-setting') }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
        <div class="row g-3">
        <div class="col-md-6"><label class="form-label">{{ __('App Name') }}</label><input type="text" name="app_name" value="{{ setting('app_name') }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('Version') }}</label><input type="text" name="version" value="{{ setting('version') }}" class="form-control"></div>
        <div class="col-12"><label class="form-label">{{ __('Copyright Text') }}</label><input type="text" name="copyright_text" value="{{ setting('copyright_text') }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('Logo') }}</label><input type="file" name="logo" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('Favicon') }}</label><input type="file" name="favicon" class="form-control"></div>
        </div><div class="mt-3"><button class="btn btn-primary">{{ __('Save') }}</button></div></form>
    </div></div></div></div>
@endsection
