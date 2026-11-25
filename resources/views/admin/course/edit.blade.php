@extends('admin.master_layout')
@section('title')
    <title>Courses — Edit | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Courses — Edit</h1>
        <a href="{{ route('admin.course.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.course.update', $item->id) }}" method="POST" enctype="multipart/form-data">
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
<label class="form-label" for="instructor_id">Instructor</label>
<select name="instructor_id" id="instructor_id" class="form-select @error('instructor_id') is-invalid @enderror">
<option value="">{{ __('Select') }}</option>
@foreach ($instructors ?? [] as $opt)
<option value="{{ $opt->id }}" {{ (old('instructor_id', $item->instructor_id ?? '')) == $opt->id ? 'selected' : '' }}>{{ $opt->name ?? $opt->title ?? $opt->id }}</option>
@endforeach
</select>
@error('instructor_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="type">Type</label>
<select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
<option value="course" {{ (old('type', $item->type?->value ?? '')) == 'course' ? 'selected' : '' }}>Course</option>
<option value="webinar" {{ (old('type', $item->type?->value ?? '')) == 'webinar' ? 'selected' : '' }}>Webinar</option>
</select>
@error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="price">Price</label>
<input type="number" step="any" name="price" id="price" value="{{ old('price', $item->price ?? '') }}" class="form-control @error('price') is-invalid @enderror">
@error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="discount">Discount</label>
<input type="number" step="any" name="discount" id="discount" value="{{ old('discount', $item->discount ?? '') }}" class="form-control @error('discount') is-invalid @enderror">
@error('discount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="start_date">Start Date</label>
<input type="date" name="start_date" id="start_date" value="{{ old('start_date', $item->start_date ?? '') }}" class="form-control @error('start_date') is-invalid @enderror">
@error('start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="duration">Duration (minutes)</label>
<input type="number" step="any" name="duration" id="duration" value="{{ old('duration', $item->duration ?? '') }}" class="form-control @error('duration') is-invalid @enderror">
@error('duration')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="thumbnail">Thumbnail</label>
<input type="file" name="thumbnail" id="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror">
@if(!empty($item->thumbnail))<div class="mt-2"><img src="{{ \App\Models\Media::resolveUrl($item->thumbnail) ?? asset('images/placeholder.svg') }}" alt="" class="img-thumbnail" style="max-height:80px;"></div>@endif
@error('thumbnail')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-12">
<label class="form-label" for="description">Description</label>
<textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $item->description ?? '') }}</textarea>
@error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-3">
<label class="form-label" for="certificate">Certificate</label>
<div class="form-check"><input type="checkbox" name="certificate" id="certificate" value="1" class="form-check-input" {{ (old('certificate', $item->certificate ?? '')) ? 'checked' : '' }}><label class="form-check-label" for="certificate">Certificate</label></div>
@error('certificate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-3">
<label class="form-label" for="downloadable">Downloadable</label>
<div class="form-check"><input type="checkbox" name="downloadable" id="downloadable" value="1" class="form-check-input" {{ (old('downloadable', $item->downloadable ?? '')) ? 'checked' : '' }}><label class="form-check-label" for="downloadable">Downloadable</label></div>
@error('downloadable')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-3">
<label class="form-label" for="qna">Q&A</label>
<div class="form-check"><input type="checkbox" name="qna" id="qna" value="1" class="form-check-input" {{ (old('qna', $item->qna ?? '')) ? 'checked' : '' }}><label class="form-check-label" for="qna">Q&A</label></div>
@error('qna')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="status">Status</label>
<select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
<option value="active" {{ (old('status', $item->status?->value ?? '')) == 'active' ? 'selected' : '' }}>Active</option>
<option value="inactive" {{ (old('status', $item->status?->value ?? '')) == 'inactive' ? 'selected' : '' }}>Inactive</option>
</select>
@error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('admin.course.index') }}" class="btn btn-light border">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div></div>
@endsection
