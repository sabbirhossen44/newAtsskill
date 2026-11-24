@extends('admin.master_layout')
@section('title')
    <title>Counter Section — Edit | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">Counter Section</h1><a href="{{ route('admin.counter-section.index') }}" class="btn btn-outline-secondary">{{ __('Back to List') }}</a></div>
    <div class="card"><div class="card-body"><form action="{{ route('admin.counter-section.update', $item->id) }}" method="POST" enctype="multipart/form-data">@csrf
    @method('PUT')
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $item->translation->title ?? $item->title ?? '') }}" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">{{ __('Subtitle') }}</label><input type="text" name="subtitle" value="{{ old('subtitle', $item->translation->subtitle ?? $item->subtitle ?? '') }}" class="form-control"></div>
    <div class="col-12"><label class="form-label">{{ __('Description') }}</label><textarea name="description" rows="4" class="form-control">{{ old('description', $item->translation->description ?? $item->description ?? '') }}</textarea></div>
    <div class="col-md-6"><label class="form-label">{{ __('Image') }}</label><input type="file" name="image" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">{{ __('Status') }}</label><select name="status" class="form-select"><option value="active" {{ ($item->status ?? '') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option><option value="inactive" {{ ($item->status ?? '') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option></select></div>
    </div><div class="mt-3"><button class="btn btn-primary">{{ __('Save') }}</button></div></form></div></div>
@endsection
