@extends('admin.master_layout')
@section('title')
    <title>Menu Builder | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <h1 class="h4 mb-3">{{ __('Menu Builder') }}</h1>
    <div class="row g-3"><div class="col-lg-4"><div class="card"><div class="card-body">
        <h5>{{ __('Menus') }}</h5>
        <ul class="list-group mb-3">@foreach ($menus ?? [] as $menu)<li class="list-group-item d-flex justify-content-between"><span>{{ $menu->translation->name ?? $menu->name ?? '' }}</span>
        <form action="{{ route('admin.menus.delete') }}" method="POST" class="d-inline">@csrf<input type="hidden" name="id" value="{{ $menu->id }}"><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></li>@endforeach</ul>
        <form action="{{ route('admin.menus.create') }}" method="POST" class="d-flex gap-2">@csrf<input type="text" name="name" class="form-control" placeholder="{{ __('Menu name') }}" required><button class="btn btn-primary">{{ __('Create') }}</button></form>
    </div></div></div>
    <div class="col-lg-8"><div class="card"><div class="card-body">
        <h5>{{ __('Menu Items') }}</h5>
        <p class="text-muted">{{ __('Add items to the selected menu. Items are stored per menu; the frontend header/footer render them from the menu builder.') }}</p>
        <form action="{{ route('admin.menus.items.create') }}" method="POST">@csrf
            <div class="row g-2"><div class="col-md-4"><input type="text" name="label" class="form-control" placeholder="{{ __('Label') }}" required></div>
            <div class="col-md-4"><input type="text" name="url" class="form-control" placeholder="{{ __('URL') }}"></div>
            <div class="col-md-4"><button class="btn btn-primary">{{ __('Add Item') }}</button></div></div></form>
        <ul class="list-group mt-3">@foreach ($menuItems ?? [] as $mi)<li class="list-group-item d-flex justify-content-between"><span>{{ $mi->translation->label ?? $mi->label ?? '' }} — {{ $mi->url ?? '' }}</span>
        <span><form action="{{ route('admin.menus.items.update') }}" method="POST" class="d-inline">@csrf<input type="hidden" name="id" value="{{ $mi->id }}"></form>
        <form action="{{ route('admin.menus.items.delete') }}" method="POST" class="d-inline">@csrf<input type="hidden" name="id" value="{{ $mi->id }}"><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></span></li>@endforeach</ul>
    </div></div></div></div>
@endsection
