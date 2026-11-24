@extends('admin.master_layout')
@section('title')
    <title>Course Sub Categories — Create | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Course Sub Categories — Create</h1>
        <a href="{{ route('admin.course-sub-category.index', $parentId ?? $item->parent_id ?? 0) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.course-sub-category.store', $parentId ?? $item->parent_id ?? 0) }}" method="POST" enctype="multipart/form-data">
            @csrf
                        <div class="row g-3">
<div class="col-md-6">
<label class="form-label" for="name">Name</label>
<input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
@error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="parent_id">Parent Category</label>
<select name="parent_id" id="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
<option value="">{{ __('Select') }}</option>
@foreach ($parentCategories ?? [] as $opt)
<option value="{{ $opt->id }}" {{ (old('parent_id')) == $opt->id ? 'selected' : '' }}>{{ $opt->name ?? $opt->title ?? $opt->id }}</option>
@endforeach
</select>
@error('parent_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="icon">Icon</label>
<input type="file" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror">
@error('icon')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="status">Status</label>
<select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
<option value="active" {{ (old('status')) == 'active' ? 'selected' : '' }}>Active</option>
<option value="inactive" {{ (old('status')) == 'inactive' ? 'selected' : '' }}>Inactive</option>
</select>
@error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('admin.course-sub-category.index', $parentId ?? $item->parent_id ?? 0) }}" class="btn btn-light border">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div></div>
@endsection
