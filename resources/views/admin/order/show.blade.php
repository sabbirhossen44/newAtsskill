@extends('admin.master_layout')
@section('title')
    <title>Order Details | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">{{ __('Order Details') }} — {{ $order->order_id ?? $order->id ?? '' }}</h1><a href="{{ route('admin.orders') }}" class="btn btn-outline-secondary">{{ __('Back to List') }}</a></div>
    <div class="row g-3"><div class="col-lg-8"><div class="card"><div class="card-body">
        <table class="table table-bordered mb-0"><tbody>
        <tr><th style="width:200px;">{{ __('Customer') }}</th><td>{{ $order->user->name ?? '' }} ({{ $order->user->email ?? '' }})</td></tr>
        <tr><th>{{ __('Total') }}</th><td>৳{{ number_format($order->total_amount ?? $order->total ?? 0, 0) }}</td></tr>
        <tr><th>{{ __('Payment Status') }}</th><td>{{ $order->payment_status ?? '' }}</td></tr>
        <tr><th>{{ __('Payment Method') }}</th><td>{{ $order->payment_method ?? '' }}</td></tr>
        <tr><th>{{ __('Status') }}</th><td>{{ $order->status ?? '' }}</td></tr>
        <tr><th>{{ __('Date') }}</th><td>{{ $order->created_at ?? '' }}</td></tr></tbody></table>
        <h5 class="mt-4">{{ __('Items') }}</h5>
        <ul class="list-group">@foreach ($order->items ?? $order->orderItems ?? [] as $oi)<li class="list-group-item d-flex justify-content-between"><span>{{ $oi->course->title ?? $oi->title ?? '' }}</span><strong>৳{{ number_format($oi->price ?? 0, 0) }}</strong></li>@endforeach</ul>
    </div></div></div>
    <div class="col-lg-4"><div class="card"><div class="card-body">
        @can('order.management')
        <form action="{{ route('admin.order.update', $order->id ?? 0) }}" method="POST">@csrf
            <div class="mb-3"><label class="form-label">{{ __('Order Status') }}</label>
            <select name="status" class="form-select"><option value="pending" {{ ($order->status ?? '') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option><option value="completed" {{ ($order->status ?? '') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option><option value="cancelled" {{ ($order->status ?? '') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option></select></div>
            <button class="btn btn-primary w-100">{{ __('Update Order') }}</button>
        </form>
        <a href="{{ route('admin.print-invoice', $order->id ?? 0) }}" class="btn btn-outline-secondary w-100 mt-2" target="_blank"><i class="fas fa-print me-1"></i>{{ __('Print Invoice') }}</a>
        @endcan
    </div></div></div></div>
@endsection
