@extends('admin.master_layout')
@section('title')
    <title>Orders | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">{{ __('Orders') }}</h1><a href="{{ route('admin.pending-orders') }}" class="btn btn-outline-warning">{{ __('Pending Orders') }}</a></div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>#</th><th>{{ __('Order') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Total') }}</th><th>{{ __('Payment') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th></tr></thead>
    <tbody>@forelse ($orders ?? [] as $item)<tr>
        <td>{{ $loop->iteration }}</td><td>{{ $item->order_id ?? $item->id }}</td><td>{{ $item->user->name ?? '' }}</td>
        <td>৳{{ number_format($item->total_amount ?? $item->total ?? 0, 0) }}</td><td>{{ $item->payment_status ?? '' }}</td><td><span class="badge bg-secondary">{{ $item->status ?? '' }}</span></td>
        <td class="text-nowrap">@can('order.management')
            <a href="{{ route('admin.order', $item->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
            <a href="{{ route('admin.print-invoice', $item->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fas fa-print"></i></a>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-url="{{ route('admin.order.destroy', $item->id) }}"><i class="fas fa-trash"></i></button>
        @endcan</td></tr>
    @empty<tr><td colspan="7" class="text-center text-muted py-4">{{ __('No records found.') }}</td></tr>@endforelse</tbody></table></div></div>
    @if(isset($orders) && method_exists($orders, 'links'))<div class="card-footer">@include('admin.paginate_box', ['items' => $orders])</div>@endif</div>
@endsection
