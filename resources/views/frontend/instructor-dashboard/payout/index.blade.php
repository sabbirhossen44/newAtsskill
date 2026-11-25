@extends('frontend.instructor-dashboard.layouts.master')
@section('dashboard-contents')
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0">{{ __('Request Payout') }}</h2><a href="{{ route('instructor.payout.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>{{ __('New Payout Request') }}</a></div>
    <div class="row g-3 mb-3"><div class="col-md-4"><div class="card"><div class="card-body"><small class="text-muted">{{ __('Available Balance') }}</small><div class="h4 mb-0">৳{{ number_format($balance ?? $availableBalance ?? 0, 0) }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><small class="text-muted">{{ __('Total Earned') }}</small><div class="h4 mb-0">৳{{ number_format($totalEarned ?? 0, 0) }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><small class="text-muted">{{ __('Total Withdrawn') }}</small><div class="h4 mb-0">৳{{ number_format($totalWithdrawn ?? 0, 0) }}</div></div></div></div></div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>{{ __('Amount') }}</th><th>{{ __('Method') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th><th>{{ __('Action') }}</th></tr></thead>
    <tbody>@forelse ($payouts ?? [] as $p)<tr><td>৳{{ number_format($p->amount ?? 0, 0) }}</td><td>{{ $p->method->name ?? $p->payment_method ?? '' }}</td><td><span class="badge bg-secondary">{{ $p->status?->value ?? '' }}</span></td><td>{{ $p->created_at ?? '' }}</td>
    <td><form action="{{ route('instructor.payout.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">{{ __('No payout requests yet.') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
