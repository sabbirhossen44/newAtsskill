{{--
    Theme-one header.
    Expects (all optional — sensible fallbacks are used):
      $navMenu        array of ['label','link','child' => [...]] (menus/menu_items)
      $cartCount      int, shared by a view composer or the controller
      $footerSettings footer_settings row (phone)
    Contact fallbacks are the live numbers from atsskill.com.
--}}
@php
    $currentLang = app()->getLocale();
    $topEmail = setting('mail_sender_email', 'info@atsskill.com');
    $topPhoneOne = $footerSettings->phone ?? '+8801624965170';
    $topPhoneTwo = '+8801345569068';
    $logoUrl = \App\Models\Media::resolveUrl(setting('logo')) ?? asset('images/logo.svg');
    $navLinks = $navMenu ?? [
        ['label' => __('Home'), 'link' => route('home')],
        ['label' => __('About Us'), 'link' => route('about-us')],
        ['label' => __('Courses'), 'link' => route('courses')],
        ['label' => __('Instructors'), 'link' => route('all-instructors')],
        ['label' => __('Blog'), 'link' => route('blogs')],
        ['label' => __('Contact'), 'link' => route('contact.index')],
        ['label' => __('Our Services'), 'link' => route('custom-page', ['slug' => 'our-services'])],
    ];
@endphp
<header class="ats-header">
    {{-- Top royal-blue bar --}}
    <div class="ats-topbar">
        <div class="container">
            <div class="row align-items-center g-2">
                <div class="col-7 col-lg-8">
                    <ul class="ats-topbar-info list-unstyled d-flex flex-wrap align-items-center gap-3 mb-0">
                        <li>
                            <a href="mailto:{{ $topEmail }}"><i class="fas fa-envelope me-1"></i><span class="d-none d-sm-inline">{{ $topEmail }}</span><span class="d-sm-none">{{ __('Email') }}</span></a>
                        </li>
                        <li class="d-none d-md-block">
                            <a href="tel:{{ $topPhoneOne }}"><i class="fas fa-phone-alt me-1"></i>{{ $topPhoneOne }}</a>
                        </li>
                        <li class="d-none d-lg-block">
                            <a href="tel:{{ $topPhoneTwo }}"><i class="fas fa-phone-alt me-1"></i>{{ $topPhoneTwo }}</a>
                        </li>
                    </ul>
                </div>
                <div class="col-5 col-lg-4">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <div class="ats-lang-toggle" role="group" aria-label="{{ __('Language') }}">
                            <a href="{{ route('set-language', ['code' => 'bn']) }}" class="{{ $currentLang === 'bn' ? 'active' : '' }}">BN</a>
                            <a href="{{ route('set-language', ['code' => 'en']) }}" class="{{ $currentLang === 'en' ? 'active' : '' }}">EN</a>
                        </div>
                        @guest
                            <a href="{{ route('login') }}" class="ats-topbar-auth"><i class="fas fa-sign-in-alt me-1"></i>{{ __('Login') }}</a>
                            <a href="{{ route('register') }}" class="ats-topbar-auth d-none d-sm-inline"><i class="fas fa-user-plus me-1"></i>{{ __('Register') }}</a>
                        @else
                            <a href="{{ auth()->user()->role === 'instructor' ? route('instructor.dashboard') : route('student.dashboard') }}" class="ats-topbar-auth"><i class="fas fa-tachometer-alt me-1"></i>{{ __('Dashboard') }}</a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- White main header --}}
    <div id="sticky-header" class="ats-main-header sticky-top bg-white shadow-sm">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-6 col-xl-3">
                    <div class="logo py-2">
                        <a href="{{ route('home') }}"><img src="{{ $logoUrl }}" alt="{{ setting('app_name', 'ATS Skill') }}"></a>
                    </div>
                </div>
                <div class="col-xl-7 d-none d-xl-block">
                    <nav class="ats-nav" aria-label="{{ __('Main navigation') }}">
                        <ul class="navigation list-unstyled d-flex justify-content-center gap-4 mb-0">
                            @foreach ($navLinks as $item)
                                <li class="{{ request()->url() === url($item['link']) ? 'active' : '' }}">
                                    <a href="{{ $item['link'] }}">{{ $item['label'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                </div>
                <div class="col-6 col-xl-2">
                    <div class="d-flex align-items-center justify-content-end gap-3">
                        <button class="btn btn-link p-0 ats-search-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#atsHeaderSearch" aria-expanded="false" aria-controls="atsHeaderSearch" aria-label="{{ __('Search') }}">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('cart') }}" class="ats-cart-link position-relative" aria-label="{{ __('Cart') }}">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="ats-cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill">{{ $cartCount ?? 0 }}</span>
                        </a>
                        @auth
                            <div class="dropdown d-none d-xl-block">
                                <a href="#" class="ats-user-menu dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="{{ \App\Models\Media::resolveUrl(auth()->user()->image) ?? asset('images/placeholder.svg') }}" alt="{{ auth()->user()->name }}" class="rounded-circle">
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ auth()->user()->role === 'instructor' ? route('instructor.dashboard') : route('student.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ auth()->user()->role === 'instructor' ? route('instructor.setting.index') : route('student.setting.index') }}">{{ __('Profile') }}</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('ats-logout-form').submit();">{{ __('Logout') }}</a>
                                        <form id="ats-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                                    </li>
                                </ul>
                            </div>
                        @endauth
                        <button class="btn btn-link p-0 d-xl-none ats-mobile-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#atsMobileMenu" aria-controls="atsMobileMenu" aria-label="{{ __('Menu') }}">
                            <span class="hamburger-bar"></span><span class="hamburger-bar"></span><span class="hamburger-bar"></span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="collapse" id="atsHeaderSearch">
                <form action="{{ route('courses') }}" method="GET" class="py-3">
                    <div class="input-group">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ __('Search For Course') }}..." aria-label="{{ __('Search For Course') }}">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</header>

{{-- Mobile drawer (Bootstrap offcanvas, replaces the old custom sliding menu) --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="atsMobileMenu" aria-labelledby="atsMobileMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="atsMobileMenuLabel">{{ setting('app_name', 'ATS Skill') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body">
        @guest
            <div class="d-flex gap-2 mb-3">
                <a href="{{ route('login') }}" class="btn btn-success flex-fill"><i class="fas fa-sign-in-alt me-1"></i>{{ __('Login') }}</a>
                <a href="{{ route('register') }}" class="btn btn-primary flex-fill"><i class="fas fa-user-plus me-1"></i>{{ __('Register') }}</a>
            </div>
        @else
            <a href="{{ auth()->user()->role === 'instructor' ? route('instructor.dashboard') : route('student.dashboard') }}" class="btn btn-primary w-100 mb-3"><i class="fas fa-tachometer-alt me-1"></i>{{ __('Dashboard') }}</a>
        @endguest
        <form action="{{ route('courses') }}" method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ __('Search For Course') }}...">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <ul class="list-unstyled ats-mobile-nav">
            @foreach ($navLinks as $item)
                <li><a href="{{ $item['link'] }}">{{ $item['label'] }}</a></li>
            @endforeach
        </ul>
        <div class="ats-lang-toggle mt-3">
            <a href="{{ route('set-language', ['code' => 'bn']) }}" class="{{ $currentLang === 'bn' ? 'active' : '' }}">বাংলা</a>
            <a href="{{ route('set-language', ['code' => 'en']) }}" class="{{ $currentLang === 'en' ? 'active' : '' }}">English</a>
        </div>
    </div>
</div>
