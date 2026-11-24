@extends('frontend.student-dashboard.layouts.master')
@section('dashboard-contents')
    <h2 class="h4 mb-3">{{ __('My Reviews') }}</h2>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>{{ __('Course') }}</th><th>{{ __('Rating') }}</th><th>{{ __('Review') }}</th><th>{{ __('Date') }}</th><th>{{ __('Action') }}</th></tr></thead>
    <tbody>@forelse ($reviews ?? [] as $r)<tr><td>{{ $r->course->title ?? '' }}</td><td>{{ str_repeat('★', (int)($r->rating ?? 0)) }}{{ str_repeat('☆', 5 - (int)($r->rating ?? 0)) }}</td>
        <td>{{ \Illuminate\Support\Str::limit($r->comment ?? $r->review ?? '', 60) }}</td><td>{{ $r->created_at ?? '' }}</td>
        <td class="text-nowrap"><a href="{{ route('student.reviews.show', $r->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
        <form action="{{ route('student.reviews.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this review?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">{{ __('No reviews yet.') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
