@extends('admin.master_layout')
@section('title')
    <title>Instructors — Create | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">Instructors — Create</h1>
    <a href="{{ route('admin.instructors.index') }}" class="btn btn-outline-secondary">{{ __('Back to List') }}</a></div>
    <div class="card"><div class="card-body"><form action="{{ route('admin.instructors.store') }}" method="POST" enctype="multipart/form-data">@csrf
    <div class="row g-3"><div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">@error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">@error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror">@error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label">Designation</label><input type="text" name="designation" value="{{ old('designation') }}" class="form-control @error('designation') is-invalid @enderror">@error('designation')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label">{{ __('Image') }}</label><input type="file" name="image" class="form-control"></div><div class="col-12"><label class="form-label">{{ __('Biography') }}</label><textarea name="bio" rows="4" class="form-control">{{ old('bio', $item->bio ?? '') }}</textarea></div></div>
    <div class="mt-4"><button class="btn btn-primary">{{ __('Save') }}</button></div></form></div></div>
@endsection
