@extends('admin.master_layout')
@section('title')
    <title>Testimonials — Create | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Testimonials — Create</h1>
        <a href="{{ route('admin.testimonial.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.testimonial.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
                        <div class="row g-3">
<div class="col-md-6">
<label class="form-label" for="name">Name</label>
<input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
@error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="designation">Designation</label>
<input type="text" name="designation" id="designation" value="{{ old('designation') }}" class="form-control @error('designation') is-invalid @enderror">
@error('designation')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="rating">Rating</label>
<input type="number" step="any" name="rating" id="rating" value="{{ old('rating') }}" class="form-control @error('rating') is-invalid @enderror">
@error('rating')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="image">Image</label>
<input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
@error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-12">
<label class="form-label" for="comment">Comment</label>
<textarea name="comment" id="comment" rows="4" class="form-control @error('comment') is-invalid @enderror">{{ old('comment') }}</textarea>
@error('comment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
                <a href="{{ route('admin.testimonial.index') }}" class="btn btn-light border">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div></div>
@endsection
