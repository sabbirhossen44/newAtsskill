{{-- Wizard step indicator (mirrors old course/navigation.blade.php). Current step: {{ $step ?? 1 }} of 4. --}}
<div class="alert alert-light border d-flex flex-wrap gap-3 mb-3"><span><strong>{{ __('Step') }} {{ $step ?? 1 }}/4</strong></span><span class="text-muted">{{ __('Basic Information') }} → {{ __('More Information') }} → {{ __('Course Content') }} → {{ __('Finish') }}</span></div>
