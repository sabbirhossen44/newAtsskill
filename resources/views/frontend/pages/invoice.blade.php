<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Invoice') }} — {{ $order->order_id ?? $order->id ?? '' }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: sans-serif; color: #222; }
    .invoice_area { max-width: 800px; margin: 0 auto; padding: 30px; }
    .invoice_header { background: #f3f2f2; padding: 30px; display: flex; justify-content: space-between; }
    .invoice_header h2 { text-transform: uppercase; font-size: 28px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background: #f3f2f2; }
    .text-end { text-align: right; }
    .no-print { margin-top: 20px; }
    @media print { .no-print { display: none; } }
</style></head>
<body><div class="invoice_area">
    <div class="invoice_header">
        <div><img src="{{ \App\Models\Media::resolveUrl(setting('logo')) ?? asset('images/placeholder.svg') }}" alt="{{ setting('app_name', 'ATS Skill') }}" style="max-height:60px;"><p>{{ setting('app_name', 'ATS Skill') }}</p></div>
        <div style="text-align:right;"><h2>{{ __('Invoice') }}</h2><p>{{ __('Order') }}: {{ $order->order_id ?? $order->id ?? '' }}</p><p>{{ __('Date') }}: {{ $order->created_at ?? '' }}</p></div>
    </div>
    <table><tbody>
        <tr><th style="width:200px;">{{ __('Billed To') }}</th><td>{{ $order->user->name ?? '' }}<br>{{ $order->user->email ?? '' }}<br>{{ $order->user->phone ?? '' }}<br>{{ $order->user->address ?? '' }}</td></tr>
        <tr><th>{{ __('Payment Method') }}</th><td>{{ $order->payment_method ?? '' }}</td></tr>
        <tr><th>{{ __('Payment Status') }}</th><td>{{ $order->payment_status ?? '' }}</td></tr>
    </tbody></table>
    <table><thead><tr><th>{{ __('Item') }}</th><th class="text-end">{{ __('Price') }}</th></tr></thead><tbody>
    @foreach ($order->items ?? $order->orderItems ?? [] as $item)
        <tr><td>{{ $item->course->title ?? $item->title ?? '' }}</td><td class="text-end">৳{{ number_format($item->price ?? 0, 0) }}</td></tr>
    @endforeach
    @if (($order->discount ?? 0) > 0)<tr><td>{{ __('Coupon Discount') }}</td><td class="text-end">− ৳{{ number_format($order->discount, 0) }}</td></tr>@endif
    <tr><th>{{ __('Total') }}</th><th class="text-end">৳{{ number_format($order->total_amount ?? $order->total ?? 0, 0) }}</th></tr>
    </tbody></table>
    <p style="margin-top:20px;" class="text-muted">{{ __('Thank you for learning with us.') }}</p>
    <div class="no-print"><button onclick="window.print()">{{ __('Print Invoice') }}</button></div>
</div></body></html>
