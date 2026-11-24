@extends('frontend.instructor-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Announcement') }}</h2>
    <div class="card"><div class="card-body"><form action="{{ route('instructor.announcements.store') }}" method="POST">@csrf
    <div class="mb-3"><label class="form-label">{{ __('Course') }}</label><select name="course_id" class="form-select" required><option value="">{{ __('Select') }}</option>@foreach ($courses ?? [] as $c)<option value="{{ $c->id }}" {{ ($announcement->course_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->title }}</option>@endforeach</select></div>
    <div class="mb-3"><label class="form-label">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $announcement->title ?? '') }}" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">{{ __('Message') }}</label><textarea name="message" rows="5" class="form-control" required>{{ old('message', $announcement->message ?? $announcement->description ?? '') }}</textarea></div>
    <button class="btn btn-primary">{{ __('Save') }}</button></form></div></div>
@endsection
