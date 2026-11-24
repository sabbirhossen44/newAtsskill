{{-- Admin sidebar (desktop) + offcanvas copy for mobile, Bootstrap 5. --}}
<aside class="admin-sidebar d-none d-lg-flex flex-column flex-shrink-0 p-3">
    <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center mb-3 text-decoration-none">
        <img src="{{ \App\Models\Media::resolveUrl(setting('logo')) ?? asset('images/placeholder.svg') }}" alt="{{ setting('app_name', 'ATS Skill') }}" class="admin-logo">
    </a>
    <ul class="nav nav-pills flex-column mb-auto admin-nav">
            @can('dashboard.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="fas fa-home me-2"></i><span>Dashboard</span></a></li>
            @endcan
            @can('course.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.course*', 'admin.courses*', 'admin.course-category*', 'admin.course-level*', 'admin.course-language*', 'admin.course-review*', 'admin.course-delete-request*') ? 'active' : '' }}" href="{{ route('admin.courses.index') }}"><i class="fas fa-book me-2"></i><span>Courses</span></a></li>
            @endcan
            @can('blog.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.blog*') ? 'active' : '' }}" href="{{ route('admin.blogs.index') }}"><i class="fas fa-blog me-2"></i><span>Blogs</span></a></li>
            @endcan
            @can('order.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.order*') ? 'active' : '' }}" href="{{ route('admin.orders') }}"><i class="fas fa-shopping-cart me-2"></i><span>Orders</span></a></li>
            @endcan
            @can('coupon.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.coupon*') ? 'active' : '' }}" href="{{ route('admin.coupon.index') }}"><i class="fas fa-ticket-alt me-2"></i><span>Coupons</span></a></li>
            @endcan
            @can('badge.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.badge*') ? 'active' : '' }}" href="{{ route('admin.badges.index') }}"><i class="fas fa-award me-2"></i><span>Badges</span></a></li>
            @endcan
            @can('faq.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.faq*') ? 'active' : '' }}" href="{{ route('admin.faq.index') }}"><i class="fas fa-question-circle me-2"></i><span>FAQs</span></a></li>
            @endcan
            @can('testimonial.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.testimonial*') ? 'active' : '' }}" href="{{ route('admin.testimonial.index') }}"><i class="fas fa-comment me-2"></i><span>Testimonials</span></a></li>
            @endcan
            @can('page.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.page-builder*') ? 'active' : '' }}" href="{{ route('admin.page-builder.index') }}"><i class="fas fa-file-alt me-2"></i><span>Pages</span></a></li>
            @endcan
            @can('menu.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.menubuilder*', 'admin.menus*') ? 'active' : '' }}" href="{{ route('admin.menubuilder.index') }}"><i class="fas fa-bars me-2"></i><span>Menu Builder</span></a></li>
            @endcan
            @can('language.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.language*') ? 'active' : '' }}" href="{{ route('admin.languages.index') }}"><i class="fas fa-language me-2"></i><span>Languages</span></a></li>
            @endcan
            @can('location.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.country*', 'admin.city*') ? 'active' : '' }}" href="{{ route('admin.country.index') }}"><i class="fas fa-map-marker-alt me-2"></i><span>Locations</span></a></li>
            @endcan
            @can('payment.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.paymentgateway*') ? 'active' : '' }}" href="{{ route('admin.paymentgateway') }}"><i class="fas fa-credit-card me-2"></i><span>Payment Gateways</span></a></li>
            @endcan
            @can('basic.payment.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.basicpayment*') ? 'active' : '' }}" href="{{ route('admin.basicpayment') }}"><i class="fas fa-university me-2"></i><span>Basic Payments</span></a></li>
            @endcan
            @can('withdraw.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.withdraw*') ? 'active' : '' }}" href="{{ route('admin.withdraw-list') }}"><i class="fas fa-wallet me-2"></i><span>Withdraws</span></a></li>
            @endcan
            @can('newsletter.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.newsletter*', 'admin.subscriber*', 'admin.send-mail*') ? 'active' : '' }}" href="{{ route('admin.subscriber-list') }}"><i class="fas fa-envelope-open-text me-2"></i><span>Newsletter</span></a></li>
            @endcan
            @can('contect.message.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.contact-message*') ? 'active' : '' }}" href="{{ route('admin.contact-messages') }}"><i class="fas fa-envelope me-2"></i><span>Contact Messages</span></a></li>
            @endcan
            @can('footer.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.footersetting*') ? 'active' : '' }}" href="{{ route('admin.footersetting.index') }}"><i class="fas fa-shoe-prints me-2"></i><span>Footer Settings</span></a></li>
            @endcan
            @can('section.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.hero-section*', 'admin.about-section*', 'admin.featured-*', 'admin.newsletter-section*', 'admin.counter-section*', 'admin.our-features-section*', 'admin.banner-section*', 'admin.faq-section*', 'admin.contact-section*') ? 'active' : '' }}" href="{{ route('admin.hero-section.index') }}"><i class="fas fa-layer-group me-2"></i><span>Frontend Sections</span></a></li>
            @endcan
            @can('appearance.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.site-appearance*', 'admin.section-setting*', 'admin.site-color-setting*') ? 'active' : '' }}" href="{{ route('admin.site-appearance.index') }}"><i class="fas fa-palette me-2"></i><span>Site Appearance</span></a></li>
            @endcan
            @can('customer.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.all-customers', 'admin.customer*', 'admin.active-customers', 'admin.non-verified-customers', 'admin.banned-customers') ? 'active' : '' }}" href="{{ route('admin.all-customers') }}"><i class="fas fa-users me-2"></i><span>Customers</span></a></li>
            @endcan
            @can('instructor.request.list')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.instructor-request*') ? 'active' : '' }}" href="{{ route('admin.instructor-request.index') }}"><i class="fas fa-chalkboard-teacher me-2"></i><span>Instructor Requests</span></a></li>
            @endcan
            @can('currency.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.currency*') ? 'active' : '' }}" href="{{ route('admin.currency.index') }}"><i class="fas fa-dollar-sign me-2"></i><span>Currencies</span></a></li>
            @endcan
            @can('brand.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.brand*') ? 'active' : '' }}" href="{{ route('admin.brand.index') }}"><i class="fas fa-tags me-2"></i><span>Brands</span></a></li>
            @endcan
            @can('social.link.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.social-link*') ? 'active' : '' }}" href="{{ route('admin.social-link.index') }}"><i class="fas fa-share-alt me-2"></i><span>Social Links</span></a></li>
            @endcan
            @can('course.certificate.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.certificate-builder*') ? 'active' : '' }}" href="{{ route('admin.certificate-builder.index') }}"><i class="fas fa-certificate me-2"></i><span>Certificates</span></a></li>
            @endcan
            @can('admin.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.admin*') ? 'active' : '' }}" href="{{ route('admin.admin.index') }}"><i class="fas fa-user-shield me-2"></i><span>Manage Admins</span></a></li>
            @endcan
            @can('role.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.role*') ? 'active' : '' }}" href="{{ route('admin.role.index') }}"><i class="fas fa-user-lock me-2"></i><span>Roles</span></a></li>
            @endcan
            @can('setting.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.settings', 'admin.general-setting', 'admin.commission-setting', 'admin.seo-setting', 'admin.custom-code', 'admin.email-configuration', 'admin.marketing-setting', 'admin.crediential-setting') ? 'active' : '' }}" href="{{ route('admin.settings') }}"><i class="fas fa-cog me-2"></i><span>Settings</span></a></li>
            @endcan
    </ul>
    <hr>
    <div class="d-flex align-items-center gap-2">
        <img src="{{ \App\Models\Media::resolveUrl(Auth::guard('admin')->user()->image ?? null) ?? asset('images/placeholder.svg') }}" alt="" class="rounded-circle" width="32" height="32">
        <div class="small"><strong class="d-block">{{ Auth::guard('admin')->user()->name ?? __('Admin') }}</strong><span class="text-muted">{{ Auth::guard('admin')->user()->email ?? '' }}</span></div>
    </div>
</aside>
<div class="offcanvas offcanvas-start" tabindex="-1" id="adminSidebarOffcanvas" aria-labelledby="adminSidebarOffcanvasLabel">
    <div class="offcanvas-header"><h5 id="adminSidebarOffcanvasLabel">{{ setting('app_name', 'ATS Skill') }}</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body">
        <ul class="nav nav-pills flex-column admin-nav">
            @can('dashboard.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="fas fa-home me-2"></i><span>Dashboard</span></a></li>
            @endcan
            @can('course.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.course*', 'admin.courses*', 'admin.course-category*', 'admin.course-level*', 'admin.course-language*', 'admin.course-review*', 'admin.course-delete-request*') ? 'active' : '' }}" href="{{ route('admin.courses.index') }}"><i class="fas fa-book me-2"></i><span>Courses</span></a></li>
            @endcan
            @can('blog.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.blog*') ? 'active' : '' }}" href="{{ route('admin.blogs.index') }}"><i class="fas fa-blog me-2"></i><span>Blogs</span></a></li>
            @endcan
            @can('order.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.order*') ? 'active' : '' }}" href="{{ route('admin.orders') }}"><i class="fas fa-shopping-cart me-2"></i><span>Orders</span></a></li>
            @endcan
            @can('coupon.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.coupon*') ? 'active' : '' }}" href="{{ route('admin.coupon.index') }}"><i class="fas fa-ticket-alt me-2"></i><span>Coupons</span></a></li>
            @endcan
            @can('badge.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.badge*') ? 'active' : '' }}" href="{{ route('admin.badges.index') }}"><i class="fas fa-award me-2"></i><span>Badges</span></a></li>
            @endcan
            @can('faq.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.faq*') ? 'active' : '' }}" href="{{ route('admin.faq.index') }}"><i class="fas fa-question-circle me-2"></i><span>FAQs</span></a></li>
            @endcan
            @can('testimonial.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.testimonial*') ? 'active' : '' }}" href="{{ route('admin.testimonial.index') }}"><i class="fas fa-comment me-2"></i><span>Testimonials</span></a></li>
            @endcan
            @can('page.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.page-builder*') ? 'active' : '' }}" href="{{ route('admin.page-builder.index') }}"><i class="fas fa-file-alt me-2"></i><span>Pages</span></a></li>
            @endcan
            @can('menu.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.menubuilder*', 'admin.menus*') ? 'active' : '' }}" href="{{ route('admin.menubuilder.index') }}"><i class="fas fa-bars me-2"></i><span>Menu Builder</span></a></li>
            @endcan
            @can('language.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.language*') ? 'active' : '' }}" href="{{ route('admin.languages.index') }}"><i class="fas fa-language me-2"></i><span>Languages</span></a></li>
            @endcan
            @can('location.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.country*', 'admin.city*') ? 'active' : '' }}" href="{{ route('admin.country.index') }}"><i class="fas fa-map-marker-alt me-2"></i><span>Locations</span></a></li>
            @endcan
            @can('payment.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.paymentgateway*') ? 'active' : '' }}" href="{{ route('admin.paymentgateway') }}"><i class="fas fa-credit-card me-2"></i><span>Payment Gateways</span></a></li>
            @endcan
            @can('basic.payment.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.basicpayment*') ? 'active' : '' }}" href="{{ route('admin.basicpayment') }}"><i class="fas fa-university me-2"></i><span>Basic Payments</span></a></li>
            @endcan
            @can('withdraw.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.withdraw*') ? 'active' : '' }}" href="{{ route('admin.withdraw-list') }}"><i class="fas fa-wallet me-2"></i><span>Withdraws</span></a></li>
            @endcan
            @can('newsletter.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.newsletter*', 'admin.subscriber*', 'admin.send-mail*') ? 'active' : '' }}" href="{{ route('admin.subscriber-list') }}"><i class="fas fa-envelope-open-text me-2"></i><span>Newsletter</span></a></li>
            @endcan
            @can('contect.message.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.contact-message*') ? 'active' : '' }}" href="{{ route('admin.contact-messages') }}"><i class="fas fa-envelope me-2"></i><span>Contact Messages</span></a></li>
            @endcan
            @can('footer.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.footersetting*') ? 'active' : '' }}" href="{{ route('admin.footersetting.index') }}"><i class="fas fa-shoe-prints me-2"></i><span>Footer Settings</span></a></li>
            @endcan
            @can('section.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.hero-section*', 'admin.about-section*', 'admin.featured-*', 'admin.newsletter-section*', 'admin.counter-section*', 'admin.our-features-section*', 'admin.banner-section*', 'admin.faq-section*', 'admin.contact-section*') ? 'active' : '' }}" href="{{ route('admin.hero-section.index') }}"><i class="fas fa-layer-group me-2"></i><span>Frontend Sections</span></a></li>
            @endcan
            @can('appearance.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.site-appearance*', 'admin.section-setting*', 'admin.site-color-setting*') ? 'active' : '' }}" href="{{ route('admin.site-appearance.index') }}"><i class="fas fa-palette me-2"></i><span>Site Appearance</span></a></li>
            @endcan
            @can('customer.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.all-customers', 'admin.customer*', 'admin.active-customers', 'admin.non-verified-customers', 'admin.banned-customers') ? 'active' : '' }}" href="{{ route('admin.all-customers') }}"><i class="fas fa-users me-2"></i><span>Customers</span></a></li>
            @endcan
            @can('instructor.request.list')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.instructor-request*') ? 'active' : '' }}" href="{{ route('admin.instructor-request.index') }}"><i class="fas fa-chalkboard-teacher me-2"></i><span>Instructor Requests</span></a></li>
            @endcan
            @can('currency.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.currency*') ? 'active' : '' }}" href="{{ route('admin.currency.index') }}"><i class="fas fa-dollar-sign me-2"></i><span>Currencies</span></a></li>
            @endcan
            @can('brand.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.brand*') ? 'active' : '' }}" href="{{ route('admin.brand.index') }}"><i class="fas fa-tags me-2"></i><span>Brands</span></a></li>
            @endcan
            @can('social.link.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.social-link*') ? 'active' : '' }}" href="{{ route('admin.social-link.index') }}"><i class="fas fa-share-alt me-2"></i><span>Social Links</span></a></li>
            @endcan
            @can('course.certificate.management')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.certificate-builder*') ? 'active' : '' }}" href="{{ route('admin.certificate-builder.index') }}"><i class="fas fa-certificate me-2"></i><span>Certificates</span></a></li>
            @endcan
            @can('admin.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.admin*') ? 'active' : '' }}" href="{{ route('admin.admin.index') }}"><i class="fas fa-user-shield me-2"></i><span>Manage Admins</span></a></li>
            @endcan
            @can('role.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.role*') ? 'active' : '' }}" href="{{ route('admin.role.index') }}"><i class="fas fa-user-lock me-2"></i><span>Roles</span></a></li>
            @endcan
            @can('setting.view')
            <li class="nav-item"><a class="nav-link {{ Route::is('admin.settings', 'admin.general-setting', 'admin.commission-setting', 'admin.seo-setting', 'admin.custom-code', 'admin.email-configuration', 'admin.marketing-setting', 'admin.crediential-setting') ? 'active' : '' }}" href="{{ route('admin.settings') }}"><i class="fas fa-cog me-2"></i><span>Settings</span></a></li>
            @endcan
        </ul>
    </div>
</div>
