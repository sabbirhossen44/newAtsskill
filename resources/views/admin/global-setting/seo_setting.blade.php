@extends('admin.master_layout')
@section('title')
    <title>SEO Settings | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="card"><div class="card-body"><form action="{{ route('admin.update-seo-setting') }}" method="POST">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label">{{ __('Meta Title') }}</label><input type="text" name="meta_title" value="{{ old('meta_title', $seo->meta_title ?? '') }}" class="form-control"></div>
        <div class="mb-3"><label class="form-label">{{ __('Meta Description') }}</label><textarea name="meta_description" rows="3" class="form-control">{{ old('meta_description', $seo->meta_description ?? '') }}</textarea></div>
        <div class="mb-3"><label class="form-label">{{ __('Meta Keywords') }}</label><input type="text" name="meta_keywords" value="{{ old('meta_keywords', $seo->meta_keywords ?? '') }}" class="form-control"></div>
        <button class="btn btn-primary">{{ __('Save') }}</button></form></div></div>
@endsection
