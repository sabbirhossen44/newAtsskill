@extends('admin.master_layout')
@section('title')
    <title>Site Appearance | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">Site Appearance</h1>
    @can('appearance.management')<a href="{{ route('admin.site-appearance.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>{{ __('Create New') }}</a>@endcan</div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>#</th><th>{{ __('Name') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th></tr></thead>
    <tbody>@forelse ($items ?? [] as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->name ?? $item->title ?? '' }}</td><td>{{ $item->status ?? '' }}</td>
    <td class="text-nowrap">@can('appearance.management')<a href="{{ route('admin.site-appearance.edit', $item->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-url="{{ route('admin.site-appearance.destroy', $item->id) }}"><i class="fas fa-trash"></i></button>@endcan</td></tr>
    @empty<tr><td colspan="4" class="text-center text-muted py-4">{{ __('No records found.') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
