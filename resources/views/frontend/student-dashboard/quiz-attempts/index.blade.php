@extends('frontend.student-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('My Quiz Attempts') }}</h2>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>{{ __('Quiz') }}</th><th>{{ __('Course') }}</th><th>{{ __('Score') }}</th><th>{{ __('Result') }}</th><th>{{ __('Date') }}</th><th></th></tr></thead>
    <tbody>@forelse ($attempts ?? [] as $a)<tr><td>{{ $a->quiz->title ?? $a->quiz_title ?? '' }}</td><td>{{ $a->course->title ?? '' }}</td>
        <td>{{ $a->obtained_marks ?? $a->score ?? '' }} / {{ $a->total_marks ?? '' }}</td><td><span class="badge {{ ($a->status ?? '') == 'passed' ? 'bg-success' : 'bg-danger' }}">{{ $a->status ?? '' }}</span></td>
        <td>{{ $a->created_at ?? '' }}</td><td><a href="{{ route('student.quiz.result', [$a->quiz_id ?? $a->id, $a->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('Result') }}</a></td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">{{ __('No quiz attempts yet.') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
