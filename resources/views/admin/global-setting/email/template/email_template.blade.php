@extends('admin.master_layout')
@section('title')
    <title>Email Templates | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0"><thead class="table-light"><tr><th>{{ __('Template') }}</th><th>{{ __('Subject') }}</th><th>{{ __('Action') }}</th></tr></thead>
    <tbody>@forelse ($templates ?? [] as $t)<tr><td>{{ $t->name ?? $t->key ?? '' }}</td><td>{{ $t->subject ?? '' }}</td>
    <td><a href="{{ route('admin.edit-email-template', $t->id) }}" class="btn btn-sm btn-outline-primary">{{ __('Edit') }}</a></td></tr>
    @empty<tr><td colspan="3" class="text-center text-muted py-3">{{ __('No email templates.') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
