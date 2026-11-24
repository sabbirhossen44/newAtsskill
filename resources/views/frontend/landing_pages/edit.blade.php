@extends('admin.master_layout')
@section('title')<title>{{ __('Landing Page') }} | {{ setting('app_name', 'ATS Skill') }}</title>@endsection
@section('admin-content')
    <h1 class="h4 mb-3">{{ __('Landing Page') }}</h1>
    <div class="card"><div class="card-body"><form action="{{ route('landing-pages.update', $landingPage->id) }}" method="POST" enctype="multipart/form-data">@csrf
    @method('PUT')
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $landingPage->title ?? '') }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">{{ __('Slug') }}</label><input type="text" name="slug" value="{{ old('slug', $landingPage->slug ?? '') }}" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">{{ __('Template') }}</label><select name="template" class="form-select"><option value="atsskill">atsskill</option><option value="atsskill2">atsskill2</option><option value="atsskill3">atsskill3</option><option value="learnhub">learnhub</option><option value="checkout">checkout</option><option value="custom">custom</option></select></div>
    <div class="col-md-6"><label class="form-label">{{ __('Status') }}</label><select name="status" class="form-select"><option value="active">{{ __('Active') }}</option><option value="inactive">{{ __('Inactive') }}</option></select></div>
    <div class="col-12"><label class="form-label">{{ __('Subtitle') }}</label><textarea name="subtitle" rows="3" class="form-control">{{ old('subtitle', $landingPage->subtitle ?? '') }}</textarea></div>
    </div><div class="mt-3"><button class="btn btn-primary">{{ __('Save') }}</button></div></form></div></div>
@endsection
