@extends('admin.master_layout')
@section('title')
    <title>Course Delete Requests — Details | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Course Delete Requests — {{ __('Details') }}</h1>
        <a href="{{ route('admin.course-delete-request.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <table class="table table-bordered mb-0"><tbody>
<tr><th scope="row" style="width:200px;">Course</th><td>{{ $item->course->title ?? '' }}</td></tr><tr><th scope="row" style="width:200px;">Instructor</th><td>{{ $item->user->name ?? '' }}</td></tr><tr><th scope="row" style="width:200px;">Reason</th><td>{{ $item->reason ?? '' }}</td></tr>
        </tbody></table>
    </div></div>
@endsection
