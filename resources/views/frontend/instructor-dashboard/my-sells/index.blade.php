@extends('frontend.instructor-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('My Sales') }}</h2>
    <div class="row g-3 mb-3"><div class="col-md-4"><div class="card"><div class="card-body"><small class="text-muted">{{ __('Total Sales') }}</small><div class="h4 mb-0">৳{{ number_format($totalSales ?? 0, 0) }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><small class="text-muted">{{ __('Total Orders') }}</small><div class="h4 mb-0">{{ $totalOrders ?? 0 }}</div></div></div></div></div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>{{ __('Order') }}</th><th>{{ __('Course') }}</th><th>{{ __('Student') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Date') }}</th></tr></thead>
    <tbody>@forelse ($sales ?? $orders ?? [] as $s)<tr><td>{{ $s->order_id ?? $s->id }}</td><td>{{ $s->course->title ?? $s->title ?? '' }}</td><td>{{ $s->user->name ?? '' }}</td><td>৳{{ number_format($s->amount ?? $s->price ?? 0, 0) }}</td><td>{{ $s->created_at ?? '' }}</td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">{{ __('No sales yet.') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
