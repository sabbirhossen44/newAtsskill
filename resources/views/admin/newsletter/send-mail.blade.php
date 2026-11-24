@extends('admin.master_layout')
@section('title')
    <title>Send Mail to Subscribers | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.send-mail-to-subscriber') }}" method="POST">@csrf
            <div class="mb-3"><label class="form-label">{{ __('Subject') }}</label><input type="text" name="subject" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">{{ __('Message') }}</label><textarea name="message" rows="6" class="form-control" required></textarea></div>
            <button class="btn btn-primary">{{ __('Send Mail') }}</button></form>
    </div></div>
@endsection
