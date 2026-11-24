@extends('frontend.instructor-dashboard.layouts.master')
@section('dashboard-contents')
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0">{{ __('Announcements') }}</h2><a href="{{ route('instructor.announcements.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>{{ __('Create') }}</a></div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>{{ __('Title') }}</th><th>{{ __('Course') }}</th><th>{{ __('Date') }}</th><th>{{ __('Action') }}</th></tr></thead>
    <tbody>@forelse ($announcements ?? [] as $a)<tr><td>{{ $a->title ?? '' }}</td><td>{{ $a->course->title ?? '' }}</td><td>{{ $a->created_at ?? '' }}</td>
    <td class="text-nowrap"><a href="{{ route('instructor.announcements.edit', $a->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
    <form action="{{ route('instructor.announcements.destroy', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></td></tr>
    @empty<tr><td colspan="4" class="text-center text-muted py-4">{{ __('No announcements yet.') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
