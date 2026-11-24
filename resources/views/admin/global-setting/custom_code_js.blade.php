@extends('admin.master_layout')
@section('title')
    <title>Custom JS | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <a href="{{ route('admin.custom-code') }}" class="btn btn-primary">{{ __('Open Custom Code') }}</a>
@endsection
