@extends('admin.master_layout')
@section('title')
    <title>Certificate Builder — Edit | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Certificate Builder — Edit</h1>
        <a href="{{ route('admin.certificate-builder.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.certificate-builder.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
<div class="col-md-12">
<label class="form-label" for="title">Title</label>
<input type="text" name="title" id="title" value="{{ old('title', $item->title ?? '') }}" class="form-control @error('title') is-invalid @enderror" required>
@error('title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="background_image">Background Image</label>
<input type="file" name="background_image" id="background_image" class="form-control @error('background_image') is-invalid @enderror">
@if(!empty($item->background_image))<div class="mt-2"><img src="{{ \App\Models\Media::resolveUrl($item->background_image) ?? asset('images/placeholder.svg') }}" alt="" class="img-thumbnail" style="max-height:80px;"></div>@endif
@error('background_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
                <a href="{{ route('admin.certificate-builder.index') }}" class="btn btn-light border">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div></div>
@endsection
