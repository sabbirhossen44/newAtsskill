@php
    $lesson = $lesson ?? null;
    $storage = $lesson->storage ?? $lesson->demo_video_storage ?? 'upload';
    $source = $lesson->file_path ?? $lesson->source ?? $lesson->video_url ?? '';
    $isYoutube = str_contains((string) $source, 'youtube') || str_contains((string) $source, 'youtu.be');
    $isVimeo = str_contains((string) $source, 'vimeo');
@endphp
@if (!$lesson)
    <div class="text-center text-white p-5"><i class="fas fa-play-circle fa-3x mb-3"></i><p class="mb-0">{{ __('Select a lesson to start learning.') }}</p></div>
@elseif ($storage == 'youtube' || $isYoutube)
    <iframe src="{{ $source }}" class="w-100 h-100" allowfullscreen title="{{ $lesson->title ?? '' }}"></iframe>
@elseif ($storage == 'vimeo' || $isVimeo)
    <iframe src="{{ $source }}" class="w-100 h-100" allowfullscreen title="{{ $lesson->title ?? '' }}"></iframe>
@elseif ($storage == 'google_drive')
    <iframe src="{{ $source }}" class="w-100 h-100" allowfullscreen title="{{ $lesson->title ?? '' }}"></iframe>
@elseif ($storage == 'iframe')
    <div class="w-100 h-100">{!! $source !!}</div>
@elseif ($storage == 'external_link')
    <div class="text-center text-white p-5"><p>{{ __('This lesson opens on an external site.') }}</p><a href="{{ $source }}" target="_blank" rel="noopener" class="btn btn-light">{{ __('Open Lesson') }}</a></div>
@elseif ($storage == 'live')
    <div class="text-center text-white p-5"><i class="fas fa-video fa-3x mb-3"></i><p>{{ __('Live class — join from the schedule.') }}</p>
    @if (($lesson->live_class->type ?? '') == 'zoom') @include('frontend.pages.learning-player.partials.live.zoom', ['lesson' => $lesson])
    @elseif (($lesson->live_class->type ?? '') == 'jitsi') @include('frontend.pages.learning-player.partials.live.jitsi', ['lesson' => $lesson]) @endif</div>
@else
    <video controls class="w-100 h-100" src="{{ \App\Models\Media::resolveUrl($source) ?? asset('images/placeholder.svg') }}" poster="{{ \App\Models\Media::resolveUrl($course->thumbnail ?? null) ?? asset('images/placeholder.svg') }}"></video>
@endif
