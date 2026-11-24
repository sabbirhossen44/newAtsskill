<!DOCTYPE html><html><head><meta charset="UTF-8"><title>{{ __('Live Class Scheduled') }}</title></head>
<body style="font-family:sans-serif; color:#222; line-height:1.6; max-width:600px; margin:0 auto; padding:20px;">
<div style="border-bottom:2px solid #1d4ed8; padding-bottom:10px; margin-bottom:20px;"><strong>{{ setting('app_name', 'ATS Skill') }}</strong></div>
<p>{{ __('Hello') }},</p><p>{{ __('A live class has been scheduled for') }} <strong>{{ $course->title ?? '' }}</strong>.</p><p>{{ __('Join here') }}: <a href="{{ $joinUrl ?? '#' }}">{{ $joinUrl ?? '' }}</a></p>
<p style="color:#666; font-size:13px; margin-top:30px;">{{ setting('app_name', 'ATS Skill') }} — {{ __('This is an automated email, please do not reply.') }}</p>
</body></html>
