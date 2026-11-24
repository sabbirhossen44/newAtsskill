{{--
    Theme-one footer (royal blue).
    Expects (all optional): $footerSettings (footer_settings row), $socialLinks
    (social_links), $footerMenuOne/Two/Three (menu items). Payment banner is
    display-only: bKash / Nagad / Rocket / SSLCOMMERZ are live merchants to be
    confirmed later — NO gateway secrets are stored or rendered here.
--}}
@php
    $footerLogo = \App\Models\Media::resolveUrl($footerSettings->logo ?? null)
        ?? \App\Models\Media::resolveUrl(setting('logo'))
        ?? asset('images/logo.svg');
    $footerText = $footerSettings->footer_text ?? __('ATS Skill — learn real tech skills from expert mentors and turn them into income.');
    $footerAddress = $footerSettings->address ?? 'Holding # 927, Manikpur, Munshiganj Sadar, Munshiganj-1500, Dhaka, Bangladesh';
    $paymentBanner = asset('images/footer/payment-methods.jpg');
@endphp
<footer class="footer__area ats-footer">
    <div class="footer__top">
        <div class="container">
            <div class="row g-4">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="footer__widget">
                        <div class="logo mb-3">
                            <a href="{{ route('home') }}"><img src="{{ $footerLogo }}" alt="{{ setting('app_name', 'ATS Skill') }}"></a>
                        </div>
                        <p>{{ $footerText }}</p>
                        <div class="footerMap mt-3">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3657.4458875480154!2d90.53312837620707!3d23.552424196346195!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755ade89853b8bf%3A0x4194104552725921!2s1500%20Jubilee%20Rd%2C%20Munshiganj%2C%20Bangladesh!5e0!3m2!1sen!2sus!4v1792762476627!5m2!1sen!2sus"
                                width="100" height="150" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade" title="{{ __('ATS Skill location map') }}"></iframe>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="footer__widget">
                        <h4 class="footer__widget-title">{{ __('Useful Links') }}</h4>
                        <ul class="list-unstyled footer__link">
                            @forelse ($footerMenuOne ?? [] as $link)
                                <li><a href="{{ url($link['link']) }}">{{ $link['label'] }}</a></li>
                            @empty
                                <li><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                                <li><a href="{{ route('courses') }}">{{ __('Courses') }}</a></li>
                                <li><a href="{{ route('all-instructors') }}">{{ __('Instructors') }}</a></li>
                                <li><a href="{{ route('blogs') }}">{{ __('Blog') }}</a></li>
                                <li><a href="{{ route('about-us') }}">{{ __('About Us') }}</a></li>
                                <li><a href="{{ route('contact.index') }}">{{ __('Contact') }}</a></li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="footer__widget">
                        <h4 class="footer__widget-title">{{ __('Our Company') }}</h4>
                        <ul class="list-unstyled footer__link">
                            @forelse ($footerMenuTwo ?? [] as $link)
                                <li><a href="{{ url($link['link']) }}">{{ $link['label'] }}</a></li>
                            @empty
                                <li><a href="{{ route('about-us') }}">{{ __('About Us') }}</a></li>
                                <li><a href="{{ route('become-instructor') }}">{{ __('Become an Instructor') }}</a></li>
                                <li><a href="{{ route('custom-page', ['slug' => 'privacy-policy']) }}">{{ __('Privacy Policy') }}</a></li>
                                <li><a href="{{ route('custom-page', ['slug' => 'terms-conditions']) }}">{{ __('Terms & Conditions') }}</a></li>
                                <li><a href="{{ route('custom-page', ['slug' => 'refund-policy']) }}">{{ __('Refund Policy') }}</a></li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="footer__widget">
                        <h4 class="footer__widget-title">{{ __('Get In Touch') }}</h4>
                        <p>{{ $footerSettings->get_in_touch_text ?? '' }}</p>
                        <ul class="list-unstyled footer__contact-list">
                            <li><i class="fas fa-map-marker-alt me-2"></i><span>{{ __('Address') }}: {{ $footerAddress }}</span></li>
                            <li><i class="fas fa-phone-alt me-2"></i><a href="tel:+8801624965170">{{ __('Cell') }}: +8801624965170</a></li>
                            <li><i class="fas fa-phone-alt me-2"></i><a href="tel:+8801345569068">{{ __('Cell') }}: +8801345569068</a></li>
                            <li><i class="fas fa-envelope me-2"></i><a href="mailto:info@atsskill.com">info@atsskill.com</a></li>
                        </ul>
                        @if (!empty($socialLinks))
                            <ul class="list-inline footer__social mt-3">
                                @foreach ($socialLinks as $social)
                                    <li class="list-inline-item">
                                        <a href="{{ $social->link }}" target="_blank" rel="noopener">
                                            @if ($social->icon)
                                                <img src="{{ \App\Models\Media::resolveUrl($social->icon) ?? asset('images/placeholder.svg') }}" alt="">
                                            @else
                                                <i class="fas fa-globe"></i>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Payment methods banner (display only — no secrets, no gateway logic) --}}
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <p class="ats-payment-label mb-2">{{ __('We accept') }}</p>
                    <ul class="list-inline ats-payment-badges mb-2">
                        <li class="list-inline-item"><span class="ats-pay-badge ats-pay-bkash">bKash</span></li>
                        <li class="list-inline-item"><span class="ats-pay-badge ats-pay-nagad">Nagad</span></li>
                        <li class="list-inline-item"><span class="ats-pay-badge ats-pay-rocket">Rocket</span></li>
                        <li class="list-inline-item"><span class="ats-pay-badge ats-pay-ssl">SSLCOMMERZ</span></li>
                    </ul>
                    <img src="{{ $paymentBanner }}" alt="{{ __('Payment methods: bKash, Nagad, Rocket, SSLCOMMERZ') }}"
                        class="ats-payment-banner img-fluid rounded" onerror="this.style.display='none'">
                    <p class="small mt-2 mb-0">{{ __('Payments are processed through our live merchants. Gateway credentials are configured in the server environment only.') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="footer__bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <p class="mb-0">© {{ now()->year }} {{ setting('copyright_text') ?: setting('app_name', 'ATS Skill') }}. {{ __('All rights reserved.') }}</p>
                </div>
                <div class="col-md-5">
                    <ul class="list-inline mb-0 text-md-end footer__bottom-menu">
                        @foreach ($footerMenuThree ?? [] as $link)
                            <li class="list-inline-item"><a href="{{ url($link['link']) }}">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
