@extends('admin.master_layout')
@section('title')
    <title>Courses | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    @php $show_route = null; $edit_route = 'admin.course.edit'; $destroy_route = 'admin.course.destroy'; @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Courses</h1>
        <div class="d-flex gap-2">
            @can('course.management')
                <a href="{{ route('admin.course.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>{{ __('Create New') }}</a>
            @endcan
        </div>
    </div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th scope="col">#</th><th scope="col">Title</th><th scope="col">Instructor</th><th scope="col">Category</th><th scope="col">Price</th><th scope="col">Approval</th><th scope="col">{{ __('Status') }}</th><th scope="col">{{ __('Action') }}</th></tr></thead>
                    <tbody>
                    @forelse ($courses ?? [] as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->title ?? '' }}</td><td>{{ $item->instructor->name ?? '' }}</td><td>{{ $item->category->translation->name ?? '' }}</td><td>{{ '৳' . number_format($item->price ?? 0, 0) }}</td><td>{{ $item->is_approved ?? '' }}</td><td>
                    <form action="{{ route('admin.courses.status-update', $item->id) }}" method="POST" class="d-inline status-form">
                        @csrf @method('PUT')
                        <select name="status" class="form-select form-select-sm status-select" onchange="this.form.submit()">
                            <option value="active" {{ ($item->status ?? '') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="inactive" {{ ($item->status ?? '') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </select>
                    </form>
                </td><td class="text-nowrap">
                    @can('course.management')
                    @if($show_route)
                    <a href="{{ route($show_route, $item->id) }}" class="btn btn-sm btn-outline-info" title="{{ __('View') }}"><i class="fas fa-eye"></i></a>
                    @endif
                    @if($edit_route)
                    <a href="{{ route($edit_route, $item->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}"><i class="fas fa-edit"></i></a>
                    @endif
                    @if($destroy_route)
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-url="{{ route($destroy_route, $item->id) }}" title="{{ __('Delete') }}"><i class="fas fa-trash"></i></button>
                    @endif
                    @endcan
                </td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="text-center text-muted py-4">{{ __('No records found.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(isset($courses) && method_exists($courses, 'links'))
        <div class="card-footer">@include('admin.paginate_box', ['items' => $courses])</div>
        @endif
    </div>
@endsection
