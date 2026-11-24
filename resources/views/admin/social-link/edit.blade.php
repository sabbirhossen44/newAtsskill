@extends('admin.master_layout')
@section('title')
    <title>Social Links — Edit | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Social Links — Edit</h1>
        <a href="{{ route('admin.social-link.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.social-link.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
<div class="col-md-6">
<label class="form-label" for="name">Name</label>
<input type="text" name="name" id="name" value="{{ old('name', $item->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
@error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="url">URL</label>
<input type="text" name="url" id="url" value="{{ old('url', $item->url ?? '') }}" class="form-control @error('url') is-invalid @enderror">
@error('url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="icon">Icon</label>
<input type="text" name="icon" id="icon" value="{{ old('icon', $item->icon ?? '') }}" class="form-control @error('icon') is-invalid @enderror">
@error('icon')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="status">Status</label>
<select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
<option value="active" {{ (old('status', $item->status ?? '')) == 'active' ? 'selected' : '' }}>Active</option>
<option value="inactive" {{ (old('status', $item->status ?? '')) == 'inactive' ? 'selected' : '' }}>Inactive</option>
</select>
@error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('admin.social-link.index') }}" class="btn btn-light border">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div></div>
@endsection
