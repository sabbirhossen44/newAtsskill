@extends('admin.master_layout')
@section('title')<title>{{ __('Landing Pages') }} | {{ setting('app_name', 'ATS Skill') }}</title>@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">{{ __('Landing Pages') }}</h1>
    @can('page.management')<a href="{{ route('landing-pages.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>{{ __('Create New') }}</a>@endcan</div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>#</th><th>{{ __('Title') }}</th><th>{{ __('Slug') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th></tr></thead>
    <tbody>@forelse ($landingPages ?? [] as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->title ?? '' }}</td><td>{{ $item->slug ?? '' }}</td><td>{{ enum_value($item->status) ?? '' }}</td>
    <td class="text-nowrap"><a href="{{ route('landing-pages.edit', $item->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-url="{{ route('landing-pages.destroy', $item->id) }}"><i class="fas fa-trash"></i></button></td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">{{ __('No landing pages yet.') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
