@extends('admin.master_layout')
@section('title')
    <title>Refunds | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="alert alert-warning">{{ __('Refund module is disabled in this installation. This page is a stub — enable the Refund module to manage refunds.') }}</div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0"><thead class="table-light"><tr><th>#</th><th>{{ __('Order') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody><tr><td colspan="4" class="text-center text-muted py-4">{{ __('No refund records — module disabled.') }}</td></tr></tbody></table></div></div></div>
@endsection
