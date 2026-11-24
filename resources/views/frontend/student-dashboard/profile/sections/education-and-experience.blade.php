<div class="card mb-3"><div class="card-body">
        <h5>{{ __('Education & Experience') }}</h5>
        <div class="d-flex gap-2 mb-3"><button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#educationModal">{{ __('Add Education') }}</button>
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#experienceModal">{{ __('Add Experience') }}</button></div>
        <h6>{{ __('Education') }}</h6><ul class="list-group mb-3">@forelse ($user->educations ?? $educations ?? [] as $e)<li class="list-group-item d-flex justify-content-between"><span><strong>{{ $e->degree ?? $e->title ?? '' }}</strong> — {{ $e->institute ?? $e->institution ?? '' }} ({{ $e->passing_year ?? $e->year ?? '' }})</span>
        <span class="text-nowrap"><a href="{{ route('student.setting.edit-education-modal', $e->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
        <form action="{{ route('student.setting.education.destroy', $e->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></span></li>
        @empty<li class="list-group-item text-muted">{{ __('No education added.') }}</li>@endforelse</ul>
        <h6>{{ __('Experience') }}</h6><ul class="list-group">@forelse ($user->experiences ?? $experiences ?? [] as $e)<li class="list-group-item d-flex justify-content-between"><span><strong>{{ $e->designation ?? $e->title ?? '' }}</strong> — {{ $e->company ?? $e->organization ?? '' }} ({{ $e->start_date ?? '' }} – {{ $e->end_date ?? __('Present') }})</span>
        <span class="text-nowrap"><a href="{{ route('student.setting.edit-experience-modal', $e->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
        <form action="{{ route('student.setting.experience.destroy', $e->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></span></li>
        @empty<li class="list-group-item text-muted">{{ __('No experience added.') }}</li>@endforelse</ul>
    </div></div>
