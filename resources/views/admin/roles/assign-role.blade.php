@extends('admin.master_layout')
@section('title')
    <title>Assign Role | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <h1 class="h4 mb-3">{{ __('Assign Role') }}</h1>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.role.assign.update') }}" method="POST">@csrf @method('PUT')
            <div class="row g-3">
            <div class="col-md-6"><label class="form-label">{{ __('Admin') }}</label><select name="admin_id" class="form-select" required><option value="">{{ __('Select') }}</option>@foreach ($admins ?? [] as $a)<option value="{{ $a->id }}">{{ $a->name }} ({{ $a->email }})</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Role') }}</label><select name="role_id" class="form-select" required><option value="">{{ __('Select') }}</option>@foreach ($roles ?? [] as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach</select></div>
            </div>
            <div class="mt-3"><button class="btn btn-primary">{{ __('Assign') }}</button></div></form>
    </div></div>
@endsection
