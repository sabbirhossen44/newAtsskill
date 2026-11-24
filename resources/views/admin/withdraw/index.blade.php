@extends('admin.master_layout')
@section('title')
    <title>Withdraw Requests | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">{{ __('Withdraw Requests') }}</h1><a href="{{ route('admin.pending-withdraw-list') }}" class="btn btn-outline-warning">{{ __('Pending Withdraws') }}</a></div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>#</th><th>{{ __('Instructor') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Method') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th></tr></thead>
    <tbody>@forelse ($withdraws ?? [] as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->user->name ?? '' }}</td><td>৳{{ number_format($item->amount ?? 0, 0) }}</td><td>{{ $item->method->name ?? '' }}</td><td><span class="badge bg-secondary">{{ $item->status ?? '' }}</span></td>
    <td>@can('withdraw.management')<a href="{{ route('admin.show-withdraw', $item->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-url="{{ route('admin.delete-withdraw', $item->id) }}"><i class="fas fa-trash"></i></button>@endcan</td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">{{ __('No records found.') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
