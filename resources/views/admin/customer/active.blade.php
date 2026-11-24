@extends('admin.master_layout')
@section('title')
    <title>Active Customers | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <p class="text-muted">{{ __('Active customers.') }} <a href="{{ route('admin.all-customers') }}">{{ __('All Customers') }}</a></p>
@endsection
