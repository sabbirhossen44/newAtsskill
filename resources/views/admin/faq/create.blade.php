@extends('admin.master_layout')
@section('title')
    <title>FAQs — Create | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">FAQs — Create</h1>
        <a href="{{ route('admin.faq.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.faq.store') }}" method="POST">
            @csrf
                        <div class="row g-3">
<div class="col-md-12">
<label class="form-label" for="question">Question</label>
<input type="text" name="question" id="question" value="{{ old('question') }}" class="form-control @error('question') is-invalid @enderror" required>
@error('question')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-12">
<label class="form-label" for="answer">Answer</label>
<textarea name="answer" id="answer" rows="4" class="form-control @error('answer') is-invalid @enderror">{{ old('answer') }}</textarea>
@error('answer')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
                <a href="{{ route('admin.faq.index') }}" class="btn btn-light border">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div></div>
@endsection
