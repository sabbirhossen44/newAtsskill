@extends('admin.master_layout')
@section('title')
    <title>Instructor Quick Contact | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="card"><div class="card-body"><form action="{{ route('admin.update-email-template', $template->id ?? 0) }}" method="POST">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label">{{ __('Subject') }}</label><input type="text" name="subject" value="{{ old('subject', $template->subject ?? '') }}" class="form-control"></div>
        <div class="mb-3"><label class="form-label">{{ __('Body') }}</label><textarea name="body" rows="8" class="form-control">{{ old('body', $template->body ?? '') }}</textarea></div>
        <p class="text-muted small">{{ __('Available variables depend on the template — see the old mail view for the exact placeholders.') }}</p>
        <button class="btn btn-primary">{{ __('Save Template') }}</button></form></div></div>
@endsection
