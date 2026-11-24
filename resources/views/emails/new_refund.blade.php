<!DOCTYPE html><html><head><meta charset="UTF-8"><title>{{ __('New Refund Request') }}</title></head>
<body style="font-family:sans-serif; color:#222; line-height:1.6; max-width:600px; margin:0 auto; padding:20px;">
<div style="border-bottom:2px solid #1d4ed8; padding-bottom:10px; margin-bottom:20px;"><strong>{{ setting('app_name', 'ATS Skill') }}</strong></div>
<p>{{ __('A new refund request was submitted for order') }} {{ $order->order_id ?? '' }}.</p>
<p style="color:#666; font-size:13px; margin-top:30px;">{{ setting('app_name', 'ATS Skill') }} — {{ __('This is an automated email, please do not reply.') }}</p>
</body></html>
