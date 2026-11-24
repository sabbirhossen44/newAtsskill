@extends('frontend.instructor-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('Lesson Questions') }}</h2>
    <div class="card"><div class="card-body">
    @forelse ($questions ?? [] as $q)
        <div class="border rounded p-3 mb-3"><div class="d-flex justify-content-between"><strong>{{ $q->user->name ?? '' }} — {{ $q->lesson->title ?? '' }}</strong>
        <span><form action="{{ route('instructor.lesson-question.seen-update', $q->id) }}" method="POST" class="d-inline">@csrf @method('PUT')<button class="btn btn-sm btn-outline-secondary">{{ ($q->is_seen ?? false) ? __('Mark Unread') : __('Mark Read') }}</button></form>
        <form action="{{ route('instructor.lesson-question.destroy', $q->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></span></div>
        <p class="mb-2">{{ $q->question ?? '' }}</p>
        @foreach ($q->replies ?? [] as $r)<div class="ps-3 border-start mb-1"><small><strong>{{ $r->user->name ?? '' }}:</strong> {{ $r->reply ?? '' }}</small>
        <form action="{{ route('instructor.lesson-reply.destroy', $r->id) }}" method="POST" class="d-inline">@csrf @method('DELETE')<button class="btn btn-link btn-sm text-danger p-0">{{ __('Delete') }}</button></form></div>@endforeach
        <form action="{{ route('instructor.lesson-question.reply', $q->id) }}" method="POST" class="mt-2">@csrf<div class="input-group"><input type="text" name="reply" class="form-control" placeholder="{{ __('Write a reply') }}" required><button class="btn btn-primary">{{ __('Reply') }}</button></div></form></div>
    @empty<p class="text-muted">{{ __('No lesson questions yet.') }}</p>@endforelse
    </div></div>
@endsection
