@extends('frontend.instructor-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Course Orders') }}</h2>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>{{ __('Order') }}</th><th>{{ __('Course') }}</th><th>{{ __('Student') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Date') }}</th></tr></thead>
    <tbody>@forelse ($orders ?? [] as $o)<tr><td>{{ $o->order_id ?? $o->id }}</td><td>{{ $o->course->title ?? '' }}</td><td>{{ $o->user->name ?? '' }}</td><td>৳{{ number_format($o->amount ?? 0, 0) }}</td><td>{{ $o->created_at ?? '' }}</td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">{{ __('No orders yet.') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
