@extends('admin.master_layout')
@section('title')
    <title>Section Settings — Create | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <h1 class="h4 mb-3">Section Settings</h1>
    <div class="card"><div class="card-body"><form action="{{ route('admin.section-setting.store') }}" method="POST" enctype="multipart/form-data">@csrf
    <div class="row g-3">
    <div class="col-md-6"><label class="form-label">{{ __('Name') }}</label><input type="text" name="name" value="{{ old('name', $item->name ?? '') }}" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">{{ __('Status') }}</label><select name="status" class="form-select"><option value="active">{{ __('Active') }}</option><option value="inactive">{{ __('Inactive') }}</option></select></div>
    <div class="col-12"><label class="form-label">{{ __('Value / Settings (JSON)') }}</label><textarea name="value" rows="5" class="form-control">{{ old('value', $item->value ?? '') }}</textarea></div>
    </div><div class="mt-3"><button class="btn btn-primary">{{ __('Save') }}</button></div></form></div></div>
@endsection
