@extends('admin.master_layout')
@section('title')
    <title>Instructors | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">Instructors</h1>
    @can('customer.view')<a href="{{ route('admin.instructors.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>{{ __('Create New') }}</a>@endcan</div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>#</th><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Status</th><th>{{ __('Action') }}</th></tr></thead><tbody>
    @forelse ($instructors ?? [] as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->name ?? $item->user->name ?? '' }}</td><td>{{ $item->email ?? $item->user->email ?? '' }}</td><td>{{ $item->status ?? '' }}</td>
    <td class="text-nowrap">@can('customer.view')
        <a href="{{ route('admin.instructors.edit', $item->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
        <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-url="{{ route('admin.instructors.destroy', $item->id) }}"><i class="fas fa-trash"></i></button>
    @endcan</td></tr>
    @empty<tr><td colspan="8" class="text-center text-muted py-4">{{ __('No records found.') }}</td></tr>@endforelse</tbody></table></div></div>
    @if(isset($instructors) && method_exists($instructors, 'links'))<div class="card-footer">@include('admin.paginate_box', ['items' => $instructors])</div>@endif</div>
@endsection
