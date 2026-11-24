@extends('admin.master_layout')
@section('title')
    <title>Customers | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    @php $show_route = 'admin.customer-show'; $edit_route = 'admin.customer-show'; $destroy_route = 'admin.customer-delete'; @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Customers</h1>
        <div class="d-flex gap-2">
            
        </div>
    </div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th scope="col">#</th><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Phone</th><th scope="col">Status</th><th scope="col">{{ __('Action') }}</th></tr></thead>
                    <tbody>
                    @forelse ($customers ?? [] as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->name ?? '' }}</td><td>{{ $item->email ?? '' }}</td><td>{{ $item->phone ?? '' }}</td><td>{{ $item->status ?? '' }}</td><td class="text-nowrap">
                    @can('customer.view')
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
        @if(isset($customers) && method_exists($customers, 'links'))
        <div class="card-footer">@include('admin.paginate_box', ['items' => $customers])</div>
        @endif
    </div>
@endsection
