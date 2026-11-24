@extends('admin.master_layout')
@section('title')
    <title>Withdraw Details | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">{{ __('Withdraw Details') }}</h1><a href="{{ route('admin.withdraw-list') }}" class="btn btn-outline-secondary">{{ __('Back to List') }}</a></div>
    <div class="card"><div class="card-body">
        <table class="table table-bordered"><tbody>
        <tr><th style="width:200px;">{{ __('Instructor') }}</th><td>{{ $withdraw->user->name ?? '' }}</td></tr>
        <tr><th>{{ __('Amount') }}</th><td>৳{{ number_format($withdraw->amount ?? 0, 0) }}</td></tr>
        <tr><th>{{ __('Method') }}</th><td>{{ $withdraw->method->name ?? '' }}</td></tr>
        <tr><th>{{ __('Status') }}</th><td>{{ $withdraw->status ?? '' }}</td></tr></tbody></table>
        @can('withdraw.management')
        <form action="{{ route('admin.update-withdraw-status', $withdraw->id ?? 0) }}" method="POST" class="d-flex gap-2">@csrf @method('PUT')
            <select name="status" class="form-select" style="max-width:220px;"><option value="pending">{{ __('Pending') }}</option><option value="approved">{{ __('Approved') }}</option><option value="rejected">{{ __('Rejected') }}</option></select>
            <button class="btn btn-primary">{{ __('Update Status') }}</button></form>
        @endcan
    </div></div>
@endsection
