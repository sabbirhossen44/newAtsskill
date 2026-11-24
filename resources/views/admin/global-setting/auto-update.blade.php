@extends('admin.master_layout')
@section('title')
    <title>System Update | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="card"><div class="card-body"><p class="text-muted">{{ __('System update status.') }}</p>
    <form action="{{ route('admin.system-update.store') }}" method="POST" enctype="multipart/form-data">@csrf
    <div class="mb-3"><label class="form-label">{{ __('Update File (.zip)') }}</label><input type="file" name="update_file" class="form-control"></div>
    <button class="btn btn-primary">{{ __('Upload Update') }}</button></form></div></div>
@endsection
