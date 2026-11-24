@extends('admin.master_layout')
@section('title')
    <title>Instructor Requests — Edit | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Instructor Requests — Edit</h1>
        <a href="{{ route('admin.instructor-request.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.instructor-request.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
<div class="col-md-6">
<label class="form-label" for="status">Status</label>
<select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
<option value="pending" {{ (old('status', $item->status ?? '')) == 'pending' ? 'selected' : '' }}>Pending</option>
<option value="approved" {{ (old('status', $item->status ?? '')) == 'approved' ? 'selected' : '' }}>Approved</option>
<option value="rejected" {{ (old('status', $item->status ?? '')) == 'rejected' ? 'selected' : '' }}>Rejected</option>
</select>
@error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('admin.instructor-request.index') }}" class="btn btn-light border">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div></div>
@endsection
