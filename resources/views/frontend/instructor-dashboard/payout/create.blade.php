@extends('frontend.instructor-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Request Payout') }}</h2>
    <div class="card"><div class="card-body"><form action="{{ route('instructor.payout.store') }}" method="POST">@csrf
    <div class="row g-3">
    <div class="col-md-6"><label class="form-label">{{ __('Amount') }}</label><input type="number" step="any" name="amount" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">{{ __('Withdraw Method') }}</label><select name="method_id" class="form-select" required><option value="">{{ __('Select') }}</option>@foreach ($methods ?? [] as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach</select></div>
    <div class="col-12"><label class="form-label">{{ __('Account Details') }}</label><textarea name="account_details" rows="3" class="form-control"></textarea></div>
    </div><div class="mt-3"><button class="btn btn-primary">{{ __('Submit Request') }}</button></div></form></div></div>
@endsection
