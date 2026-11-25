@extends('frontend.instructor-dashboard.layouts.master')
@section('dashboard-contents')
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0">{{ __('My Courses') }}</h2>
    <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>{{ __('Create Course') }}</a></div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>{{ __('Course') }}</th><th>{{ __('Price') }}</th><th>{{ __('Enrollments') }}</th><th>{{ __('Status') }}</th><th>{{ __('Approval') }}</th><th>{{ __('Action') }}</th></tr></thead>
    <tbody>@forelse ($courses ?? [] as $course)<tr>
        <td><div class="d-flex gap-2 align-items-center"><img src="{{ \App\Models\Media::resolveUrl($course->thumbnail ?? null) ?? asset('images/placeholder.svg') }}" width="48" height="36" class="rounded object-fit-cover" alt=""><span>{{ $course->title ?? '' }}</span></div></td>
        <td>৳{{ number_format($course->price ?? 0, 0) }}</td><td>{{ $course->enrollments_count ?? 0 }}</td>
        <td><span class="badge bg-secondary">{{ $course->status?->value ?? '' }}</span></td><td><span class="badge {{ ($course->is_approved?->value ?? '') == 'approved' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $course->is_approved?->value ?? '' }}</span></td>
        <td class="text-nowrap"><a href="{{ route('instructor.courses.edit', $course->id) }}" class="btn btn-sm btn-outline-primary">{{ __('Edit') }}</a>
        <a href="{{ route('instructor.courses.edit-view', $course->id) }}" class="btn btn-sm btn-outline-secondary">{{ __('Content') }}</a>
        <a href="{{ route('instructor.course.delete-request.show', $course->id) }}" class="btn btn-sm btn-outline-danger">{{ __('Delete Request') }}</a></td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">{{ __('No courses yet.') }}</td></tr>@endforelse</tbody></table></div></div>
    @if(isset($courses) && method_exists($courses, 'links'))<div class="card-footer">{{ $courses->links() }}</div>@endif</div>
@endsection
