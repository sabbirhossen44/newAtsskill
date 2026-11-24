<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('meta_title', setting('app_name', 'ATS Skill'))</title>
    <meta name="description" content="@yield('meta_description', setting('app_name', 'ATS Skill') . ' — ' . __('Learn tech skills and turn them into income.'))">
    @stack('custom_meta')
    <link rel="shortcut icon" type="image/x-icon" href="{{ \App\Models\Media::resolveUrl(setting('favicon')) ?? asset('images/favicon.png') }}">
    @include('frontend.layouts.styles')
    @stack('styles')
</head>
<body>
    {{-- Scroll to top --}}
    <button class="ats-scroll-top" data-target="html" type="button" aria-label="{{ __('Back to top') }}">↑</button>

    @include('frontend.layouts.header')

    <main class="main-area">
        @if (session('success') || session('error') || session('messege'))
            <div class="container mt-3">
                <div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
                    {{ session('success') ?? session('error') ?? session('messege') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                </div>
            </div>
        @endif
        @yield('contents')
    </main>

    @include('frontend.partials.modal')
    @include('frontend.layouts.footer')
    @include('frontend.layouts.scripts')
    @stack('scripts')
</body>
</html>
