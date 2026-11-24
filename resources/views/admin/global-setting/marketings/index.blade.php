@extends('admin.master_layout')
@section('title')
    <title>Marketing Settings | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="card"><div class="card-body"><form action="{{ route('admin.update-data-layer') }}" method="POST">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label">{{ __('Data Layer / GTM') }}</label><textarea name="data_layer" rows="5" class="form-control">{{ old('data_layer', $marketing->data_layer ?? '') }}</textarea></div>
        <button class="btn btn-primary">{{ __('Save') }}</button></form></div></div>
@endsection
