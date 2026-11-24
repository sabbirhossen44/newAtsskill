@extends('admin.master_layout')
@section('title')
    <title>Edit Profile | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <h1 class="h4 mb-3">{{ __('Edit Profile') }}</h1>
    <div class="row g-3"><div class="col-lg-6"><div class="card"><div class="card-body">
        <h5>{{ __('Profile Information') }}</h5>
        <form action="{{ route('admin.profile-update') }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
            <div class="mb-3"><label class="form-label">{{ __('Name') }}</label><input type="text" name="name" value="{{ old('name', $admin->name ?? auth('admin')->user()->name ?? '') }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">{{ __('Email') }}</label><input type="email" name="email" value="{{ old('email', $admin->email ?? auth('admin')->user()->email ?? '') }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">{{ __('Image') }}</label><input type="file" name="image" class="form-control"></div>
            <button class="btn btn-primary">{{ __('Update Profile') }}</button></form>
    </div></div></div>
    <div class="col-lg-6"><div class="card"><div class="card-body">
        <h5>{{ __('Change Password') }}</h5>
        <form action="{{ route('admin.update-password') }}" method="POST">@csrf @method('PUT')
            <div class="mb-3"><label class="form-label">{{ __('Current Password') }}</label><input type="password" name="current_password" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">{{ __('New Password') }}</label><input type="password" name="password" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">{{ __('Confirm Password') }}</label><input type="password" name="password_confirmation" class="form-control" required></div>
            <button class="btn btn-primary">{{ __('Update Password') }}</button></form>
    </div></div></div></div>
@endsection
