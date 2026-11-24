<div class="card mt-3"><div class="card-header"><ul class="nav nav-tabs card-header-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#lp-overview" type="button">{{ __('Overview') }}</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#lp-qna" type="button">{{ __('Q&A') }}</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#lp-reviews" type="button">{{ __('Reviews') }}</button></li>
</ul></div>
<div class="card-body"><div class="tab-content">
    <div class="tab-pane fade show active" id="lp-overview">{!! $course->description ?? '' !!}</div>
    <div class="tab-pane fade" id="lp-qna">
        <form action="{{ route('student.qna.create') }}" method="POST" class="mb-3">@csrf
            <input type="hidden" name="lesson_id" value="{{ $currentLesson->id ?? '' }}"><input type="hidden" name="course_id" value="{{ $course->id ?? '' }}">
            <div class="input-group"><input type="text" name="question" class="form-control" placeholder="{{ __('Ask a question about this lesson') }}" required><button class="btn btn-primary">{{ __('Ask') }}</button></div></form>
        <div id="lessonQuestions">@foreach ($lessonQuestions ?? [] as $q) @include('frontend.pages.learning-player.partials.question-card', ['question' => $q]) @endforeach</div>
    </div>
    <div class="tab-pane fade" id="lp-reviews">
        <form action="{{ route('student.add-review') }}" method="POST" class="mb-3">@csrf
            <input type="hidden" name="course_id" value="{{ $course->id ?? '' }}">
            <div class="row g-2"><div class="col-md-3"><select name="rating" class="form-select"><option value="5">5 ★</option><option value="4">4 ★</option><option value="3">3 ★</option><option value="2">2 ★</option><option value="1">1 ★</option></select></div>
            <div class="col-md-7"><input type="text" name="comment" class="form-control" placeholder="{{ __('Write a review') }}" required></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('Submit') }}</button></div></div></form>
        @foreach ($course->reviews ?? [] as $r) @include('frontend.pages.learning-player.partials.review-card', ['review' => $r]) @endforeach
    </div>
</div></div></div>
