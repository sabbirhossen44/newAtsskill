@extends('admin.master_layout')
@section('title')
    <title>Clear Cache | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="card"><div class="card-body"><p>{{ __('Clear application, config and route caches.') }}</p>
    <form action="{{ route('admin.cache-clear-confirm') }}" method="POST">@csrf<button class="btn btn-warning">{{ __('Clear All Caches') }}</button></form></div></div>
@endsection
