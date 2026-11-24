@extends('admin.master_layout')
@section('title')
    <title>Refund Details | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="alert alert-warning">{{ __('Refund module is disabled in this installation.') }}</div>
    <a href="{{ route('admin.refund-request') }}" class="btn btn-outline-secondary">{{ __('Back to List') }}</a>
@endsection
