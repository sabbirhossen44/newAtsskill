@extends('admin.master_layout')
@section('title')
    <title>Dashboard | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <h1 class="h4 mb-3">{{ __('Dashboard') }}</h1>
    <div class="row g-3 mb-4">
        @php $d = $data ?? []; @endphp
        @foreach ([['total_orders', __('Total Orders'), 'fas fa-shopping-cart', 'primary'], ['total_pending_orders', __('Pending Orders'), 'fas fa-clock', 'warning'], ['total_courses', __('Total Courses'), 'fas fa-book', 'success'], ['total_students', __('Total Students'), 'fas fa-users', 'info'], ['total_instructors', __('Total Instructors'), 'fas fa-chalkboard-teacher', 'secondary'], ['total_revenue', __('Total Revenue'), 'fas fa-money-bill-wave', 'dark']] as [$key, $label, $icon, $color])
        <div class="col-6 col-md-4 col-xl-2"><div class="card stat-card h-100"><div class="card-body d-flex gap-3 align-items-center">
            <span class="stat-icon bg-{{ $color }} bg-opacity-10 text-{{ $color }}"><i class="{{ $icon }}"></i></span>
            <div><div class="h5 mb-0">{{ is_numeric($d[$key] ?? null) ? number_format($d[$key]) : ($d[$key] ?? '—') }}</div><small class="text-muted">{{ $label }}</small></div>
        </div></div></div>
        @endforeach
    </div>
    <div class="row g-3">
        <div class="col-lg-7"><div class="card"><div class="card-header d-flex justify-content-between"><strong>{{ __('Recent Orders') }}</strong><a href="{{ route('admin.orders') }}" class="small">{{ __('View all') }}</a></div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>{{ __('Order') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Total') }}</th><th>{{ __('Status') }}</th></tr></thead>
            <tbody>@forelse ($recentOrders ?? $d['recent_orders'] ?? [] as $o)<tr><td>{{ $o->order_id ?? $o->id }}</td><td>{{ $o->user->name ?? '' }}</td><td>৳{{ number_format($o->total_amount ?? $o->total ?? 0, 0) }}</td><td><span class="badge bg-secondary">{{ $o->status?->value ?? '' }}</span></td></tr>
            @empty<tr><td colspan="4" class="text-center text-muted py-3">{{ __('No recent orders.') }}</td></tr>@endforelse</tbody></table></div></div></div></div>
        <div class="col-lg-5"><div class="card"><div class="card-header d-flex justify-content-between"><strong>{{ __('Recent Courses') }}</strong><a href="{{ route('admin.courses.index') }}" class="small">{{ __('View all') }}</a></div>
            <div class="list-group list-group-flush">@forelse ($recentCourses ?? $d['recent_courses'] ?? [] as $c)<a href="{{ route('admin.course.edit', $c->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between"><span>{{ $c->title ?? '' }}</span><span class="badge bg-secondary">{{ $c->status?->value ?? '' }}</span></a>
            @empty<span class="list-group-item text-muted">{{ __('No recent courses.') }}</span>@endforelse</div></div></div>
    </div>
@endsection
