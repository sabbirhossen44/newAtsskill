<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('title', setting('app_name', 'ATS Skill') . ' — Admin')</title>
@include('admin.partials.styles')</head>
<body class="admin-auth-body">
<main class="container d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="card admin-auth-card w-100" style="max-width:420px;"><div class="card-body p-4 p-md-5">
        <div class="text-center mb-4"><img src="{{ \App\Models\Media::resolveUrl(setting('logo')) ?? asset('images/placeholder.svg') }}" alt="{{ setting('app_name', 'ATS Skill') }}" style="max-height:64px;"><h1 class="h5 mt-3 mb-0">{{ setting('app_name', 'ATS Skill') }} — {{ __('Admin') }}</h1></div>
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        @yield('auth-content')
    </div></div>
</main>
@include('admin.partials.javascripts')
</body></html>
