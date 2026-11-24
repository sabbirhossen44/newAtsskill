@extends('admin.master_layout')
@section('title')
    <title>General Settings | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <p class="text-muted">{{ __('General settings section.') }}</p>
    <a href="{{ route('admin.general-setting') }}" class="btn btn-primary">{{ __('Open General Settings') }}</a>
@endsection
