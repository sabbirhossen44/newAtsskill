@extends('admin.master_layout')
@section('title')
    <title>Blog Comments — Edit | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Blog Comments — Edit</h1>
        <a href="{{ route('admin.blog-comment.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.blog-comment.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
<div class="col-md-12">
<label class="form-label" for="comment">Comment</label>
<textarea name="comment" id="comment" rows="4" class="form-control @error('comment') is-invalid @enderror">{{ old('comment', $item->comment ?? '') }}</textarea>
@error('comment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
                <a href="{{ route('admin.blog-comment.index') }}" class="btn btn-light border">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div></div>
@endsection
