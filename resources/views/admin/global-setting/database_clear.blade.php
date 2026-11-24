@extends('admin.master_layout')
@section('title')
    <title>Clear Database | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="card"><div class="card-body"><div class="alert alert-danger">{{ __('This removes demo data. This cannot be undone.') }}</div>
    <form action="{{ route('admin.database-clear-success') }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')">@csrf<button class="btn btn-danger">{{ __('Clear Database') }}</button></form></div></div>
@endsection
