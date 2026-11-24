@extends('admin.master_layout')
@section('title')
    <title>Customers — Details | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Customers — {{ __('Details') }}</h1>
        <a href="{{ route('admin.all-customers') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <table class="table table-bordered mb-0"><tbody>
<tr><th scope="row" style="width:200px;">Name</th><td>{{ $item->name ?? '' }}</td></tr><tr><th scope="row" style="width:200px;">Email</th><td>{{ $item->email ?? '' }}</td></tr><tr><th scope="row" style="width:200px;">Phone</th><td>{{ $item->phone ?? '' }}</td></tr><tr><th scope="row" style="width:200px;">Status</th><td>{{ $item->status ?? '' }}</td></tr>
        </tbody></table>
    </div></div>
@endsection
