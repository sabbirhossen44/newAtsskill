@extends('admin.master_layout')
@section('title')
    <title>Email Configuration | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="card"><div class="card-body"><form action="{{ route('admin.update-email-configuration') }}" method="POST">@csrf @method('PUT')
        <div class="row g-3">
        <div class="col-md-6"><label class="form-label">{{ __('Mail Driver') }}</label><input type="text" name="mail_driver" value="{{ old('mail_driver', $emailConfig->mail_driver ?? '') }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('Mail Host') }}</label><input type="text" name="mail_host" value="{{ old('mail_host', $emailConfig->mail_host ?? '') }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('Mail Port') }}</label><input type="number" name="mail_port" value="{{ old('mail_port', $emailConfig->mail_port ?? '') }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('Mail Username') }}</label><input type="text" name="mail_username" value="{{ old('mail_username', $emailConfig->mail_username ?? '') }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('Mail Encryption') }}</label><input type="text" name="mail_encryption" value="{{ old('mail_encryption', $emailConfig->mail_encryption ?? '') }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('Sender Email') }}</label><input type="email" name="mail_sender_email" value="{{ old('mail_sender_email', setting('mail_sender_email')) }}" class="form-control"></div>
        </div><div class="mt-3 d-flex gap-2"><button class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('admin.test-mail-credentials') }}" class="btn btn-outline-secondary">{{ __('Send Test Mail') }}</a></div></form></div></div>
@endsection
