@extends('frontend.student-dashboard.layouts.master')
@section('dashboard-contents')
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0">{{ __('Order Details') }} — {{ $order->order_id ?? $order->id ?? '' }}</h2>
    <a href="{{ route('student.order.print-invoice', $order->id ?? 0) }}" class="btn btn-outline-secondary btn-sm" target="_blank"><i class="fas fa-print me-1"></i>{{ __('Invoice') }}</a></div>
    <div class="card"><div class="card-body">
        <table class="table table-bordered"><tbody>
        <tr><th style="width:200px;">{{ __('Date') }}</th><td>{{ $order->created_at ?? '' }}</td></tr>
        <tr><th>{{ __('Payment Method') }}</th><td>{{ $order->payment_method ?? '' }}</td></tr>
        <tr><th>{{ __('Payment Status') }}</th><td>{{ $order->payment_status ?? '' }}</td></tr>
        <tr><th>{{ __('Status') }}</th><td>{{ enum_value($order->status) ?? '' }}</td></tr>
        <tr><th>{{ __('Total') }}</th><td>৳{{ number_format($order->total_amount ?? $order->total ?? 0, 0) }}</td></tr></tbody></table>
        <h5 class="mt-3">{{ __('Items') }}</h5>
        <ul class="list-group">@foreach ($order->items ?? $order->orderItems ?? [] as $item)<li class="list-group-item d-flex justify-content-between"><span>{{ $item->course->title ?? $item->title ?? '' }}</span><strong>৳{{ number_format($item->price ?? 0, 0) }}</strong></li>@endforeach</ul>
    </div></div>
@endsection
