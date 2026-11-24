@extends('admin.master_layout')
@section('title')
    <title>Blog Comments — Details | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Blog Comments — {{ __('Details') }}</h1>
        <a href="{{ route('admin.blog-comment.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <table class="table table-bordered mb-0"><tbody>
<tr><th scope="row" style="width:200px;">Blog</th><td>{{ $item->blog->translation->title ?? '' }}</td></tr><tr><th scope="row" style="width:200px;">User</th><td>{{ $item->user->name ?? '' }}</td></tr><tr><th scope="row" style="width:200px;">Comment</th><td>{{ $item->comment ?? '' }}</td></tr>
        </tbody></table>
    </div></div>
@endsection
