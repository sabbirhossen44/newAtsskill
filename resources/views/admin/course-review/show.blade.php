@extends('admin.master_layout')
@section('title')
    <title>Course Reviews — Details | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Course Reviews — {{ __('Details') }}</h1>
        <a href="{{ route('admin.course-review.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <table class="table table-bordered mb-0"><tbody>
<tr><th scope="row" style="width:200px;">Course</th><td>{{ $item->course->title ?? '' }}</td></tr><tr><th scope="row" style="width:200px;">User</th><td>{{ $item->user->name ?? '' }}</td></tr><tr><th scope="row" style="width:200px;">Rating</th><td>{{ $item->rating ?? '' }}</td></tr><tr><th scope="row" style="width:200px;">Status</th><td>{{ $item->status ?? '' }}</td></tr>
        </tbody></table>
    </div></div>
@endsection
