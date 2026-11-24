@php $header_admin = Auth::guard('admin')->user(); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('title')
    <link rel="icon" href="{{ \App\Models\Media::resolveUrl(setting('favicon')) ?? asset('images/placeholder.svg') }}">
    @include('admin.partials.styles')
    @stack('css')
</head>
<body>
<div class="admin-shell d-flex">
    @include('admin.sidebar')
    <div class="admin-main flex-grow-1 min-vw-0">
        <nav class="navbar navbar-expand-lg admin-topbar sticky-top px-3">
            <button class="btn btn-outline-secondary btn-sm d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebarOffcanvas" aria-controls="adminSidebarOffcanvas"><i class="fas fa-bars"></i></button>
            <a class="navbar-brand d-lg-none fw-bold" href="{{ route('admin.dashboard') }}">{{ setting('app_name', 'ATS Skill') }}</a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" id="themeToggle" title="{{ __('Toggle theme') }}"><i class="fas fa-moon"></i></button>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('home') }}" target="_blank"><i class="fas fa-external-link-alt me-1"></i>{{ __('Visit Site') }}</a>
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ \App\Models\Media::resolveUrl($header_admin->image ?? null) ?? asset('images/placeholder.svg') }}" alt="" class="rounded-circle" width="28" height="28">
                        <span class="d-none d-md-inline">{{ $header_admin->name ?? __('Admin') }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.edit-profile') }}">{{ __('Edit Profile') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings') }}">{{ __('Settings') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">{{ __('Logout') }}</a></li>
                    </ul>
                </div>
                <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </nav>
        <main class="admin-content p-3 p-md-4">
            @if (session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if (session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('admin-content')
        </main>
        <footer class="admin-footer px-4 py-3 text-muted small border-top">{{ setting('copyright_text', setting('app_name', 'ATS Skill')) }}</footer>
    </div>
</div>
@include('admin.partials.modal')
@include('admin.partials.javascripts')
@stack('scripts')
</body>
</html>
