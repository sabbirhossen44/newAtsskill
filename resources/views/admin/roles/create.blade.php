@extends('admin.master_layout')
@section('title')
    <title>Roles — Create | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">Roles — Create</h1>
    <a href="{{ route('admin.role.index') }}" class="btn btn-outline-secondary">{{ __('Back to List') }}</a></div>
    <div class="card"><div class="card-body"><form action="{{ route('admin.role.store') }}" method="POST" enctype="multipart/form-data">@csrf
    <div class="row g-3"><div class="col-md-6"><label class="form-label">Role Name</label><input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">@error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-12"><label class="form-label">{{ __('Permissions') }}</label><div class="row g-2">@foreach ($permissions ?? [] as $p)<div class="col-md-4"><div class="form-check"><input type="checkbox" name="permissions[]" value="{{ $p->name ?? $p->id }}" class="form-check-input" id="perm-{{ $p->id }}"><label class="form-check-label" for="perm-{{ $p->id }}">{{ $p->name ?? '' }}</label></div></div>@endforeach</div></div></div>
    <div class="mt-4"><button class="btn btn-primary">{{ __('Save') }}</button></div></form></div></div>
@endsection
