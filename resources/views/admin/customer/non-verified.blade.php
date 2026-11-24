@extends('admin.master_layout')
@section('title')
    <title>Non-Verified Customers | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <p class="text-muted">{{ __('Non-verified customers.') }} <a href="{{ route('admin.all-customers') }}">{{ __('All Customers') }}</a></p>
@endsection
