@extends('frontend.instructor-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Edit Course') }} — {{ $course->title ?? '' }}</h2>
<div class="card mb-3"><div class="card-body"><ul class="nav nav-pills flex-wrap gap-2">
    <li class="nav-item"><a class="nav-link {{ ($step ?? 1) == 1 ? 'active' : '' }}" href="{{ isset($course) ? route('instructor.courses.edit', [$course->id, 1]) : route('instructor.courses.create') }}">{{ __('Basic Information') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ ($step ?? 1) == 2 ? 'active' : '' }}" href="{{ isset($course) ? route('instructor.courses.edit', [$course->id, 2]) : '#' }}">{{ __('More Information') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ ($step ?? 1) == 3 ? 'active' : '' }}" href="{{ isset($course) ? route('instructor.courses.edit', [$course->id, 3]) : '#' }}">{{ __('Course Content') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ ($step ?? 1) == 4 ? 'active' : '' }}" href="{{ isset($course) ? route('instructor.courses.edit', [$course->id, 4]) : '#' }}">{{ __('Finish') }}</a></li>
</ul></div></div>
    @include('frontend.instructor-dashboard.course.navigation')
    <div class="card"><div class="card-body">
        <form action="{{ route('instructor.courses.update') }}" method="POST" enctype="multipart/form-data">@csrf
        <input type="hidden" name="id" value="{{ $course->id ?? '' }}"><input type="hidden" name="step" value="{{ $step ?? 1 }}">
        @if (($step ?? 1) == 1)
        <div class="row g-3">
            <div class="col-12"><label class="form-label">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $course->title ?? '') }}" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">{{ __('Category') }}</label><select name="category_id" class="form-select">@foreach ($categories ?? [] as $c)<option value="{{ $c->id }}" {{ ($course->category_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->translation->name ?? $c->name ?? '' }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Price') }}</label><input type="number" step="any" name="price" value="{{ old('price', $course->price ?? 0) }}" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Discount') }}</label><input type="number" step="any" name="discount" value="{{ old('discount', $course->discount ?? '') }}" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Thumbnail') }}</label><input type="file" name="thumbnail" class="form-control"></div>
            <div class="col-12"><label class="form-label">{{ __('Description') }}</label><textarea name="description" rows="4" class="form-control">{{ old('description', $course->description ?? '') }}</textarea></div>
        </div>
        @elseif (($step ?? 1) == 2)
            @include('frontend.instructor-dashboard.course.more-information')
        @elseif (($step ?? 1) == 3)
            @include('frontend.instructor-dashboard.course.course-content')
        @else
            @include('frontend.instructor-dashboard.course.finish')
        @endif
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary">{{ __('Update & Continue') }}</button>
        <a href="{{ route('instructor.courses.index') }}" class="btn btn-light border">{{ __('Cancel') }}</a></div>
        </form>
    </div></div>
@endsection
