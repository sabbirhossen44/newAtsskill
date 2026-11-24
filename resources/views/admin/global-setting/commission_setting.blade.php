@extends('admin.master_layout')
@section('title')
    <title>Commission Settings | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="card"><div class="card-body"><form action="{{ route('admin.update-commission-setting') }}" method="POST">@csrf @method('PUT')
        <div class="row g-3"><div class="col-md-6"><label class="form-label">{{ __('Instructor Commission (%)') }}</label><input type="number" step="any" name="instructor_commission" value="{{ old('instructor_commission', $commission->instructor_commission ?? setting('instructor_commission')) }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">{{ __('Admin Commission (%)') }}</label><input type="number" step="any" name="admin_commission" value="{{ old('admin_commission', $commission->admin_commission ?? setting('admin_commission')) }}" class="form-control"></div></div>
        <div class="mt-3"><button class="btn btn-primary">{{ __('Save') }}</button></div></form></div></div>
@endsection
