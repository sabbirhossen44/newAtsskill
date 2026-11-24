@extends('frontend.pages.learning-player.master')
@section('learning-contents')
<section class="py-5"><div class="container"><div class="row justify-content-center"><div class="col-lg-8">
    <div class="card"><div class="card-body">
        <h2 class="h4">{{ $quiz->title ?? __('Quiz') }}</h2>
        <p class="text-muted">{{ $quiz->description ?? '' }}</p>
        <form action="{{ route('student.quiz.store', $quiz->id ?? 0) }}" method="POST">@csrf
        @foreach ($quiz->questions ?? [] as $qi => $question)
            <div class="border rounded p-3 mb-3"><p class="fw-semibold mb-2">{{ $qi + 1 }}. {{ $question->title ?? $question->question ?? '' }}</p>
            @foreach ($question->answers ?? [] as $answer)
            <div class="form-check"><input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}" class="form-check-input" id="a-{{ $answer->id }}"><label class="form-check-label" for="a-{{ $answer->id }}">{{ $answer->title ?? $answer->answer ?? '' }}</label></div>
            @endforeach</div>
        @endforeach
        <button class="btn btn-primary">{{ __('Submit Quiz') }}</button></form>
    </div></div></div></div></div></section>
@endsection
