@extends('frontend.student-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Order History') }}</h2>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>{{ __('Order ID') }}</th><th>{{ __('Date') }}</th><th>{{ __('Total') }}</th><th>{{ __('Payment') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th></tr></thead>
    <tbody>@forelse ($orders ?? [] as $order)<tr>
        <td>{{ $order->order_id ?? $order->id }}</td><td>{{ $order->created_at?->format('d M Y') ?? $order->created_at }}</td>
        <td>৳{{ number_format($order->total_amount ?? $order->total ?? 0, 0) }}</td><td>{{ $order->payment_status ?? '' }}</td>
        <td><span class="badge bg-secondary">{{ $order->status?->value ?? '' }}</span></td>
        <td class="text-nowrap"><a href="{{ route('student.order.show', $order->id) }}" class="btn btn-sm btn-outline-primary">{{ __('Details') }}</a>
        <a href="{{ route('student.order.print-invoice', $order->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">{{ __('Invoice') }}</a></td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">{{ __('No orders yet.') }} <a href="{{ route('courses') }}">{{ __('Browse Courses') }}</a></td></tr>@endforelse</tbody></table></div></div>
    @if(isset($orders) && method_exists($orders, 'links'))<div class="card-footer">{{ $orders->links() }}</div>@endif</div>
@endsection
