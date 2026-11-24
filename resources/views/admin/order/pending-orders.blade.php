@extends('admin.master_layout')
@section('title')
    <title>Pending Orders | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">{{ __('Pending Orders') }}</h1><a href="{{ route('admin.orders') }}" class="btn btn-outline-secondary">{{ __('All Orders') }}</a></div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>#</th><th>{{ __('Order') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Total') }}</th><th>{{ __('Action') }}</th></tr></thead>
    <tbody>@forelse ($orders ?? [] as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->order_id ?? $item->id }}</td><td>{{ $item->user->name ?? '' }}</td><td>৳{{ number_format($item->total_amount ?? $item->total ?? 0, 0) }}</td>
    <td>@can('order.management')<a href="{{ route('admin.order', $item->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>@endcan</td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">{{ __('No pending orders.') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
