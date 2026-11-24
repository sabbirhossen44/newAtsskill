@extends('admin.master_layout')
@section('title')
    <title>Settings | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <h1 class="h4 mb-3">{{ __('Settings') }}</h1>
    <div class="row g-3"><div class="col-lg-3">@include('admin.settings.sidebar')</div>
    <div class="col-lg-9"><div class="card"><div class="card-body">
        <h5>{{ __('General Settings') }}</h5>
        <p class="text-muted">{{ __('Site name, logo, favicon, copyright, maintenance mode and cookie consent are managed here. Values come from the settings table via the setting() helper — no secrets are shown in views.') }}</p>
        <form action="{{ route('admin.update-general-setting') }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
            <div class="row g-3">
            <div class="col-md-6"><label class="form-label">{{ __('App Name') }}</label><input type="text" name="app_name" value="{{ old('app_name', setting('app_name')) }}" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Email') }}</label><input type="email" name="mail_sender_email" value="{{ old('mail_sender_email', setting('mail_sender_email')) }}" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Logo') }}</label><input type="file" name="logo" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Favicon') }}</label><input type="file" name="favicon" class="form-control"></div>
            <div class="col-12"><label class="form-label">{{ __('Copyright Text') }}</label><input type="text" name="copyright_text" value="{{ old('copyright_text', setting('copyright_text')) }}" class="form-control"></div>
            </div>
            <div class="mt-3"><button class="btn btn-primary">{{ __('Save Settings') }}</button></div></form>
        <hr>
        <form action="{{ route('admin.update-logo-favicon') }}" method="POST" enctype="multipart/form-data" class="mt-3">@csrf @method('PUT')
            <h6>{{ __('Logo & Favicon') }}</h6>
            <div class="row g-3"><div class="col-md-6"><label class="form-label">{{ __('Logo') }}</label><input type="file" name="logo" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Favicon') }}</label><input type="file" name="favicon" class="form-control"></div></div>
            <div class="mt-3"><button class="btn btn-outline-primary">{{ __('Update Logo & Favicon') }}</button></div></form>
        <hr>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.cache-clear') }}" class="btn btn-outline-secondary">{{ __('Clear Cache') }}</a>
            <a href="{{ route('admin.database-clear') }}" class="btn btn-outline-danger" onclick="return confirm('{{ __('Clear demo data? This cannot be undone.') }}')">{{ __('Clear Database') }}</a>
        </div>
    </div></div></div></div>
@endsection
