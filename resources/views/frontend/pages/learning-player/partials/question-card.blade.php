<div class="border rounded p-3 mb-2"><div class="d-flex justify-content-between"><strong>{{ $question->user->name ?? '' }}</strong><small class="text-muted">{{ $question->created_at ?? '' }}</small></div>
<p class="mb-1">{{ $question->question ?? $question->title ?? '' }}</p>
@foreach ($question->replies ?? [] as $reply) @include('frontend.pages.learning-player.partials.reply-card', ['reply' => $reply]) @endforeach
<form action="{{ route('student.create-reply') }}" method="POST" class="mt-2">@csrf<input type="hidden" name="question_id" value="{{ $question->id }}">
<div class="input-group input-group-sm"><input type="text" name="reply" class="form-control" placeholder="{{ __('Reply') }}" required><button class="btn btn-outline-primary">{{ __('Reply') }}</button></div></form></div>
