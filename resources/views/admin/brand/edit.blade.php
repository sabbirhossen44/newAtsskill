@extends('admin.master_layout')
@section('title')
    <title>Brands — Edit | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Brands — Edit</h1>
        <a href="{{ route('admin.brand.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.brand.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
<div class="col-md-6">
<label class="form-label" for="name">Name</label>
<input type="text" name="name" id="name" value="{{ old('name', $item->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
@error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="logo">Logo</label>
<input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror">
@if(!empty($item->logo))<div class="mt-2"><img src="{{ \App\Models\Media::resolveUrl($item->logo) ?? asset('images/placeholder.svg') }}" alt="" class="img-thumbnail" style="max-height:80px;"></div>@endif
@error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
                <a href="{{ route('admin.brand.index') }}" class="btn btn-light border">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div></div>
@endsection
