<div class="card"><div class="card-body"><h5>{{ __('Sort Chapters') }}</h5>
        <form action="{{ route('instructor.course-chapter.sorting.store', $course->id ?? 0) }}" method="POST">@csrf
        <ul class="list-group mb-3" id="chapterSortList">@foreach ($course->chapters ?? [] as $ch)<li class="list-group-item d-flex justify-content-between"><span>{{ $ch->title }}</span><input type="hidden" name="order[]" value="{{ $ch->id }}"></li>@endforeach</ul>
        <button class="btn btn-primary">{{ __('Save Order') }}</button></form></div></div>
