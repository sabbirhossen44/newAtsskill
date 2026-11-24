@extends('admin.master_layout')
@section('title')
    <title>Pending Withdraws | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <h1 class="h4 mb-3">{{ __('Pending Withdraws') }}</h1>
    <div class="card"><div class="card-body"><p class="text-muted mb-0">{{ __('Pending withdraw requests are listed on the withdraw list filtered by status.') }} <a href="{{ route('admin.withdraw-list') }}">{{ __('Withdraw List') }}</a></p></div></div>
@endsection
