@extends('admin.master_layout')
@section('title')
    <title>Blogs — Edit | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Blogs — Edit</h1>
        <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.blogs.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
<div class="col-md-12">
<label class="form-label" for="title">Title</label>
<input type="text" name="title" id="title" value="{{ old('title', $item->title ?? '') }}" class="form-control @error('title') is-invalid @enderror" required>
@error('title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="slug">Slug</label>
<input type="text" name="slug" id="slug" value="{{ old('slug', $item->slug ?? '') }}" class="form-control @error('slug') is-invalid @enderror">
@error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="category_id">Category</label>
<select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
<option value="">{{ __('Select') }}</option>
@foreach ($categories ?? [] as $opt)
<option value="{{ $opt->id }}" {{ (old('category_id', $item->category_id ?? '')) == $opt->id ? 'selected' : '' }}>{{ $opt->name ?? $opt->title ?? $opt->id }}</option>
@endforeach
</select>
@error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="image">Image</label>
<input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
@if(!empty($item->image))<div class="mt-2"><img src="{{ \App\Models\Media::resolveUrl($item->image) ?? asset('images/placeholder.svg') }}" alt="" class="img-thumbnail" style="max-height:80px;"></div>@endif
@error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-12">
<label class="form-label" for="description">Description</label>
<textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $item->description ?? '') }}</textarea>
@error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
                <a href="{{ route('admin.blogs.index') }}" class="btn btn-light border">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div></div>
@endsection
