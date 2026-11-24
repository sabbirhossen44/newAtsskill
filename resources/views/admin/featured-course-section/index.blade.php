@extends('admin.master_layout')
@section('title')
    <title>Featured Course Section | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">Featured Course Section</h1>
    @can('section.management')<a href="{{ route('admin.featured-course-section.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>{{ __('Create New') }}</a>@endcan</div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>#</th><th>{{ __('Title') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th></tr></thead>
    <tbody>@forelse ($sections ?? $items ?? [] as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->translation->title ?? $item->title ?? '' }}</td><td><span class="badge bg-secondary">{{ $item->status ?? '' }}</span></td>
    <td class="text-nowrap">@can('section.management')<a href="{{ route('admin.featured-course-section.edit', $item->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-url="{{ route('admin.featured-course-section.destroy', $item->id) }}"><i class="fas fa-trash"></i></button>@endcan</td></tr>
    @empty<tr><td colspan="4" class="text-center text-muted py-4">{{ __('No records found.') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
