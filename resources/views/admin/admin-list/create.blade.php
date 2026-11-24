@extends('admin.master_layout')
@section('title')
    <title>Admins — Create | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">Admins — Create</h1>
    <a href="{{ route('admin.admin.index') }}" class="btn btn-outline-secondary">{{ __('Back to List') }}</a></div>
    <div class="card"><div class="card-body"><form action="{{ route('admin.admin.store') }}" method="POST" enctype="multipart/form-data">@csrf
    <div class="row g-3"><div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">@error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">@error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label">{{ __('Password') }}</label><input type="password" name="password" class="form-control"></div><div class="col-md-6"><label class="form-label">{{ __('Role') }}</label><select name="role" class="form-select"><option value="">{{ __('Select') }}</option>@foreach ($roles ?? [] as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach</select></div><div class="col-md-6"><label class="form-label">{{ __('Status') }}</label><select name="status" class="form-select"><option value="active">{{ __('Active') }}</option><option value="inactive">{{ __('Inactive') }}</option></select></div></div>
    <div class="mt-4"><button class="btn btn-primary">{{ __('Save') }}</button></div></form></div></div>
@endsection
