@extends('admin.master_layout')
@section('title')
    <title>Banned Customers | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <p class="text-muted">{{ __('Banned customers.') }} <a href="{{ route('admin.all-customers') }}">{{ __('All Customers') }}</a></p>
@endsection
