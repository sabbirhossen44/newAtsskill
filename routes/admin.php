<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\WithdrawController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\SiteColorSettingController;
use App\Http\Controllers\Admin\SiteAppearanceController;
use App\Http\Controllers\Admin\SectionSettingController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\Admin\OurFeaturesSectionController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\NewsletterSectionController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\MenuBuilderController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\InstructorRequestSettingController;
use App\Http\Controllers\Admin\InstructorRequestController;
use App\Http\Controllers\Admin\HeroSectionController;
use App\Http\Controllers\Admin\GlobalSettingController;
use App\Http\Controllers\Admin\FooterSettingController;
use App\Http\Controllers\Admin\FeaturedInstructorSectionController;
use App\Http\Controllers\Admin\FeaturedCourseSectionController;
use App\Http\Controllers\Admin\FaqSectionController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\EmailSettingController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\CourseSubCategoryController;
use App\Http\Controllers\Admin\CourseReviewController;
use App\Http\Controllers\Admin\CourseLevelController;
use App\Http\Controllers\Admin\CourseLanguageController;
use App\Http\Controllers\Admin\CourseDeleteRequestController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseCategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CounterSectionController;
use App\Http\Controllers\Admin\ContactSectionController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CertificateBuilderController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BlogCommentController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BasicPaymentController;
use App\Http\Controllers\Admin\BannerSectionController;
use App\Http\Controllers\Admin\BadgesController;
use App\Http\Controllers\Admin\AboutSectionController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Global\CloudStorageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes — allSolution style (plan/allsolution-style-v2.md §6)
|--------------------------------------------------------------------------
| One Route::name('admin.')->group(...) — the 'admin' URI prefix is
| applied in bootstrap/app.php, verbatim allSolution — containing one
| Route::controller(XController::class)->group(...) per resource.
| Explicit routes only — no Route::resource. URIs, methods, names
| and middleware are unchanged from the old code (553 routes).
|
| Deliberate deviations from the verbatim shape, kept so the route
| list stays identical (see §8.9 of the spec):
| - No 'guest:admin' on the login routes and no throttle:5,1 on
|   POST store-login: the old routes carried no such middleware.
|   Adding either changes the route middleware and was tested and
|   reverted (login flow itself passes with it, but the hard
|   constraint is an identical route list).
| - POST logout stays outside the authenticated group, as in the
|   old code (it carried only the 'web' middleware).
| - The authenticated group keeps ['auth:admin', 'admin.auth']:
|   'admin.auth' is the alias for EnsureAdminAuthenticated, so the
|   resolved middleware is identical to the old explicit class.
| - No withTrashed(): only Course and Media use SoftDeletes and
|   neither is implicitly bound by any admin route (course routes
|   use {id}), so there is nothing for it to apply to.
| - No bulk / toggle routes: the old code had none to preserve.
*/

Route::name('admin.')->group(function () {
    // Guest (admin login / password reset) — no guest middleware here,
    // exactly as in the old code (allSolution wraps these in guest:admin,
    // but the old routes had no such middleware, so it is not added).
    Route::controller(AuthenticatedSessionController::class)->group(function () {
        Route::get('login', 'create')->name('login');
        Route::post('store-login', 'store')->name('store-login');
        Route::post('logout', 'destroy')->name('logout');
    });

    Route::controller(PasswordResetLinkController::class)->group(function () {
        Route::get('forgot-password', 'create')->name('password.request');
        Route::post('forget-password', 'custom_forget_password')->name('forget-password');
    });

    Route::controller(NewPasswordController::class)->group(function () {
        Route::get('reset-password/{token}', 'custom_reset_password_page')->name('password.reset');
        Route::post('reset-password-store/{token}', 'custom_reset_password_store')->name('password.reset-store');
    });

    // Authenticated admin area
    Route::middleware(['auth:admin', 'admin.auth'])->group(function () {
        Route::get('/', [DashboardController::class, 'dashboard']);
        Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        // Admin profile
        Route::controller(AdminProfileController::class)->group(function () {
            Route::get('edit-profile', 'edit_profile')->name('edit-profile');
            Route::put('profile-update', 'profile_update')->name('profile-update');
            Route::put('update-password', 'update_password')->name('update-password');
        });

        // Roles — assign routes first: 'assign' must win over {role}
        Route::controller(RolesController::class)->group(function () {
            Route::get('role/assign', 'assignRoleView')->name('role.assign');
            Route::post('role/assign/{id}', 'getAdminRoles')->name('role.assign.admin');
            Route::put('role/assign', 'assignRoleUpdate')->name('role.assign.update');
            Route::get('role', 'index')->name('role.index');
            Route::get('role/create', 'create')->name('role.create');
            Route::post('role', 'store')->name('role.store');
            Route::get('role/{role}', 'show')->name('role.show');
            Route::get('role/{role}/edit', 'edit')->name('role.edit');
            Route::match(['put', 'patch'], 'role/{role}', 'update')->name('role.update');
            Route::delete('role/{role}', 'destroy')->name('role.destroy');
        });

        // Team members
        Route::controller(TeamMemberController::class)->group(function () {
            Route::get('team-members', 'index')->name('team-members.index');
            Route::get('team-members/create', 'create')->name('team-members.create');
            Route::post('team-members', 'store')->name('team-members.store');
            Route::get('team-members/{team_member}', 'show')->name('team-members.show');
            Route::get('team-members/{team_member}/edit', 'edit')->name('team-members.edit');
            Route::match(['put', 'patch'], 'team-members/{team_member}', 'update')->name('team-members.update');
            Route::delete('team-members/{team_member}', 'destroy')->name('team-members.destroy');
            Route::put('team-members/status-update/{id}', 'statusUpdate')->name('team-members.status-update');
        });

        // Instructors
        Route::controller(InstructorController::class)->group(function () {
            Route::get('instructors', 'index')->name('instructors.index');
            Route::get('instructors/create', 'create')->name('instructors.create');
            Route::post('instructors', 'store')->name('instructors.store');
            Route::get('instructors/{instructor}', 'show')->name('instructors.show');
            Route::get('instructors/{instructor}/edit', 'edit')->name('instructors.edit');
            Route::match(['put', 'patch'], 'instructors/{instructor}', 'update')->name('instructors.update');
            Route::delete('instructors/{instructor}', 'destroy')->name('instructors.destroy');
            Route::put('instructors/status-update/{id}', 'statusUpdate')->name('instructors.status-update');
        });

        // Admins (no show route, as in the old code)
        Route::controller(AdminController::class)->group(function () {
            Route::get('admin', 'index')->name('admin.index');
            Route::get('admin/create', 'create')->name('admin.create');
            Route::post('admin', 'store')->name('admin.store');
            Route::get('admin/{admin}/edit', 'edit')->name('admin.edit');
            Route::match(['put', 'patch'], 'admin/{admin}', 'update')->name('admin.update');
            Route::delete('admin/{admin}', 'destroy')->name('admin.destroy');
            Route::put('admin-status/{id}', 'changeStatus')->name('admin.status');
        });

        // Settings
        Route::controller(SettingController::class)->group(function () {
            Route::get('settings', 'settings')->name('settings');
        });

        // Cloud storage
        Route::controller(CloudStorageController::class)->group(function () {
            Route::post('cloud/store', 'store')->name('cloud.store');
        });

        // ---- Admin module areas (Phase 3) ----
        // Controllers + routes for the admin.* names referenced by
        // resources/views/admin/*. URIs/methods follow the old
        // SkillGro module routes; only the missing names were added.
        Route::controller(AboutSectionController::class)->group(function () {
            Route::get('about-section', 'index')->name('about-section.index');
            Route::get('about-section/create', 'create')->name('about-section.create');
            Route::post('about-section', 'store')->name('about-section.store');
            Route::get('about-section/{id}/edit', 'edit')->name('about-section.edit');
            Route::match(['put', 'patch'], 'about-section/{id}', 'update')->name('about-section.update');
            Route::delete('about-section/{id}', 'destroy')->name('about-section.destroy');
        });

        Route::controller(BadgesController::class)->group(function () {
            Route::get('badges', 'index')->name('badges.index');
            Route::get('badges/create', 'create')->name('badges.create');
            Route::post('badges', 'store')->name('badges.store');
            Route::get('badges/{id}/edit', 'edit')->name('badges.edit');
            Route::match(['put', 'patch'], 'badges/{id}', 'update')->name('badges.update');
            Route::delete('badges/{id}', 'destroy')->name('badges.destroy');
        });

        Route::controller(BannerSectionController::class)->group(function () {
            Route::get('banner-section', 'index')->name('banner-section.index');
            Route::get('banner-section/create', 'create')->name('banner-section.create');
            Route::post('banner-section', 'store')->name('banner-section.store');
            Route::get('banner-section/{id}/edit', 'edit')->name('banner-section.edit');
            Route::match(['put', 'patch'], 'banner-section/{id}', 'update')->name('banner-section.update');
            Route::delete('banner-section/{id}', 'destroy')->name('banner-section.destroy');
        });

        Route::controller(BasicPaymentController::class)->group(function () {
            Route::get('basicpayment', 'basicpayment')->name('basicpayment');
            Route::put('update-bank-payment', 'update_bank_payment')->name('update-bank-payment');
            Route::put('update-paypal', 'update_paypal')->name('update-paypal');
            Route::put('update-stripe', 'update_stripe')->name('update-stripe');
        });

        Route::controller(BlogCategoryController::class)->group(function () {
            Route::put('blog-category/status-update/{id}', 'statusUpdate')->name('blog-category.status-update');
            Route::get('blog-category', 'index')->name('blog-category.index');
            Route::get('blog-category/create', 'create')->name('blog-category.create');
            Route::post('blog-category', 'store')->name('blog-category.store');
            Route::get('blog-category/{id}/edit', 'edit')->name('blog-category.edit');
            Route::match(['put', 'patch'], 'blog-category/{id}', 'update')->name('blog-category.update');
            Route::delete('blog-category/{id}', 'destroy')->name('blog-category.destroy');
        });

        Route::controller(BlogCommentController::class)->group(function () {
            Route::put('blog-comment/status-update/{id}', 'statusUpdate')->name('blog-comment.status-update');
            Route::get('blog-comment', 'index')->name('blog-comment.index');
            Route::get('blog-comment/{id}', 'show')->name('blog-comment.show');
            Route::put('blog-comment/{id}', 'update')->name('blog-comment.update');
            Route::delete('blog-comment/{id}', 'destroy')->name('blog-comment.destroy');
        });

        Route::controller(BlogController::class)->group(function () {
            Route::put('blogs/status-update/{id}', 'statusUpdate')->name('blogs.status-update');
            Route::get('blogs', 'index')->name('blogs.index');
            Route::get('blogs/create', 'create')->name('blogs.create');
            Route::post('blogs', 'store')->name('blogs.store');
            Route::get('blogs/{blog}/edit', 'edit')->name('blogs.edit');
            Route::match(['put', 'patch'], 'blogs/{blog}', 'update')->name('blogs.update');
            Route::delete('blogs/{blog}', 'destroy')->name('blogs.destroy');
        });

        Route::controller(BrandController::class)->group(function () {
            Route::put('brand/status-update/{id}', 'statusUpdate')->name('brand.status-update');
            Route::get('brand', 'index')->name('brand.index');
            Route::get('brand/create', 'create')->name('brand.create');
            Route::post('brand', 'store')->name('brand.store');
            Route::get('brand/{id}/edit', 'edit')->name('brand.edit');
            Route::match(['put', 'patch'], 'brand/{id}', 'update')->name('brand.update');
            Route::delete('brand/{id}', 'destroy')->name('brand.destroy');
        });

        Route::controller(CertificateBuilderController::class)->group(function () {
            Route::get('certificate-builder', 'index')->name('certificate-builder.index');
            Route::get('certificate-builder/create', 'create')->name('certificate-builder.create');
            Route::post('certificate-builder', 'store')->name('certificate-builder.store');
            Route::get('certificate-builder/{id}/edit', 'edit')->name('certificate-builder.edit');
            Route::match(['put', 'patch'], 'certificate-builder/{id}', 'update')->name('certificate-builder.update');
            Route::delete('certificate-builder/{id}', 'destroy')->name('certificate-builder.destroy');
        });

        Route::controller(ContactMessageController::class)->group(function () {
            Route::get('contact-message/{id}', 'show')->name('contact-message');
            Route::delete('contact-message-delete/{id}', 'destroy')->name('contact-message-delete');
            Route::get('contact-messages', 'index')->name('contact-messages');
        });

        Route::controller(ContactSectionController::class)->group(function () {
            Route::get('contact-section', 'index')->name('contact-section.index');
            Route::get('contact-section/create', 'create')->name('contact-section.create');
            Route::post('contact-section', 'store')->name('contact-section.store');
            Route::get('contact-section/{id}/edit', 'edit')->name('contact-section.edit');
            Route::match(['put', 'patch'], 'contact-section/{id}', 'update')->name('contact-section.update');
            Route::delete('contact-section/{id}', 'destroy')->name('contact-section.destroy');
        });

        Route::controller(CounterSectionController::class)->group(function () {
            Route::get('counter-section', 'index')->name('counter-section.index');
            Route::get('counter-section/create', 'create')->name('counter-section.create');
            Route::post('counter-section', 'store')->name('counter-section.store');
            Route::get('counter-section/{id}/edit', 'edit')->name('counter-section.edit');
            Route::match(['put', 'patch'], 'counter-section/{id}', 'update')->name('counter-section.update');
            Route::delete('counter-section/{id}', 'destroy')->name('counter-section.destroy');
        });

        Route::controller(CountryController::class)->group(function () {
            Route::get('country', 'index')->name('country.index');
            Route::get('country/create', 'create')->name('country.create');
            Route::post('country', 'store')->name('country.store');
            Route::get('country/{country}/edit', 'edit')->name('country.edit');
            Route::match(['put', 'patch'], 'country/{country}', 'update')->name('country.update');
            Route::delete('country/{country}', 'destroy')->name('country.destroy');
        });

        Route::controller(CouponController::class)->group(function () {
            Route::get('coupon', 'index')->name('coupon.index');
            Route::get('coupon/create', 'create')->name('coupon.create');
            Route::post('coupon', 'store')->name('coupon.store');
            Route::get('coupon/{id}/edit', 'edit')->name('coupon.edit');
            Route::match(['put', 'patch'], 'coupon/{id}', 'update')->name('coupon.update');
            Route::delete('coupon/{id}', 'destroy')->name('coupon.destroy');
        });

        Route::controller(CourseCategoryController::class)->group(function () {
            Route::put('course-category/status-update/{id}', 'statusUpdate')->name('course-category.status-update');
            Route::get('course-category', 'index')->name('course-category.index');
            Route::get('course-category/create', 'create')->name('course-category.create');
            Route::post('course-category', 'store')->name('course-category.store');
            Route::get('course-category/{id}/edit', 'edit')->name('course-category.edit');
            Route::match(['put', 'patch'], 'course-category/{id}', 'update')->name('course-category.update');
            Route::delete('course-category/{id}', 'destroy')->name('course-category.destroy');
        });

        Route::controller(CourseController::class)->group(function () {
            Route::post('courses/status-update/{id}', 'statusUpdate')->name('courses.status-update');
            Route::get('course', 'index')->name('course.index');
            Route::get('courses', 'index')->name('courses.index');
            Route::get('course/create', 'create')->name('course.create');
            Route::post('course', 'store')->name('course.store');
            Route::get('course/{id}/edit', 'edit')->name('course.edit');
            Route::match(['put', 'patch'], 'course/{id}', 'update')->name('course.update');
            Route::delete('course/{id}', 'destroy')->name('course.destroy');
        });

        Route::controller(CourseDeleteRequestController::class)->group(function () {
            Route::get('course-delete-request', 'index')->name('course-delete-request.index');
            Route::get('course-delete-request/{id}', 'show')->name('course-delete-request.show');
            Route::delete('course-delete-request/{id}', 'destroy')->name('course-delete-request.destroy');
        });

        Route::controller(CourseLanguageController::class)->group(function () {
            Route::put('course-language/status-update/{id}', 'statusUpdate')->name('course-language.status-update');
            Route::get('course-language', 'index')->name('course-language.index');
            Route::get('course-language/create', 'create')->name('course-language.create');
            Route::post('course-language', 'store')->name('course-language.store');
            Route::get('course-language/{id}/edit', 'edit')->name('course-language.edit');
            Route::match(['put', 'patch'], 'course-language/{id}', 'update')->name('course-language.update');
            Route::delete('course-language/{id}', 'destroy')->name('course-language.destroy');
        });

        Route::controller(CourseLevelController::class)->group(function () {
            Route::put('course-level/status-update/{id}', 'statusUpdate')->name('course-level.status-update');
            Route::get('course-level', 'index')->name('course-level.index');
            Route::get('course-level/create', 'create')->name('course-level.create');
            Route::post('course-level', 'store')->name('course-level.store');
            Route::get('course-level/{id}/edit', 'edit')->name('course-level.edit');
            Route::match(['put', 'patch'], 'course-level/{id}', 'update')->name('course-level.update');
            Route::delete('course-level/{id}', 'destroy')->name('course-level.destroy');
        });

        Route::controller(CourseReviewController::class)->group(function () {
            Route::get('course-review', 'index')->name('course-review.index');
            Route::get('course-review/{id}', 'show')->name('course-review.show');
            Route::get('course-review/{id}/edit', 'edit')->name('course-review.edit');
            Route::match(['put', 'patch'], 'course-review/{id}', 'update')->name('course-review.update');
            Route::delete('course-review/{id}', 'destroy')->name('course-review.destroy');
        });

        Route::controller(CourseSubCategoryController::class)->group(function () {
            Route::put('course-sub-category/status-update/{id}', 'statusUpdate')->name('course-sub-category.status-update');
            Route::get('course-sub-category/{parent_id}', 'index')->name('course-sub-category.index');
            Route::get('course-sub-category/{parent_id}/create', 'create')->name('course-sub-category.create');
            Route::post('course-sub-category/{parent_id}/store', 'store')->name('course-sub-category.store');
            Route::get('course-sub-category/{parent_id}/{sub_category_id}/edit', 'edit')->name('course-sub-category.edit');
            Route::put('course-sub-category/{parent_id}/{sub_category_id}/update', 'update')->name('course-sub-category.update');
            Route::delete('course-sub-category/{parent_id}/{sub_category_id}', 'destroy')->name('course-sub-category.destroy');
        });

        Route::controller(CurrencyController::class)->group(function () {
            Route::get('currency', 'index')->name('currency.index');
            Route::get('currency/create', 'create')->name('currency.create');
            Route::post('currency', 'store')->name('currency.store');
            Route::get('currency/{id}/edit', 'edit')->name('currency.edit');
            Route::match(['put', 'patch'], 'currency/{id}', 'update')->name('currency.update');
            Route::delete('currency/{id}', 'destroy')->name('currency.destroy');
        });

        Route::controller(CustomerController::class)->group(function () {
            Route::get('active-customers', 'active_customer')->name('active-customers');
            Route::get('all-customers', 'index')->name('all-customers');
            Route::get('banned-customers', 'banned_customers')->name('banned-customers');
            Route::delete('customer-delete/{id}', 'destroy')->name('customer-delete');
            Route::get('customer-show/{id}', 'show')->name('customer-show');
            Route::get('non-verified-customers', 'non_verified_customers')->name('non-verified-customers');
        });

        Route::controller(EmailSettingController::class)->group(function () {
            Route::get('edit-email-template/{id}', 'edit_email_template')->name('edit-email-template');
            Route::get('email-configuration', 'email_config')->name('email-configuration');
            Route::post('test/mail/credentials', 'test_mail_credentials')->name('test-mail-credentials');
            Route::put('update-email-configuration', 'update_email_config')->name('update-email-configuration');
            Route::put('update-email-template/{id}', 'update_email_template')->name('update-email-template');
        });

        Route::controller(FaqController::class)->group(function () {
            Route::put('faq/status-update/{id}', 'statusUpdate')->name('faq.status-update');
            Route::get('faq', 'index')->name('faq.index');
            Route::get('faq/create', 'create')->name('faq.create');
            Route::post('faq', 'store')->name('faq.store');
            Route::get('faq/{id}/edit', 'edit')->name('faq.edit');
            Route::match(['put', 'patch'], 'faq/{id}', 'update')->name('faq.update');
            Route::delete('faq/{id}', 'destroy')->name('faq.destroy');
        });

        Route::controller(FaqSectionController::class)->group(function () {
            Route::get('faq-section', 'index')->name('faq-section.index');
            Route::get('faq-section/create', 'create')->name('faq-section.create');
            Route::post('faq-section', 'store')->name('faq-section.store');
            Route::get('faq-section/{id}/edit', 'edit')->name('faq-section.edit');
            Route::match(['put', 'patch'], 'faq-section/{id}', 'update')->name('faq-section.update');
            Route::delete('faq-section/{id}', 'destroy')->name('faq-section.destroy');
        });

        Route::controller(FeaturedCourseSectionController::class)->group(function () {
            Route::get('featured-course-section', 'index')->name('featured-course-section.index');
            Route::get('featured-course-section/create', 'create')->name('featured-course-section.create');
            Route::post('featured-course-section', 'store')->name('featured-course-section.store');
            Route::get('featured-course-section/{id}/edit', 'edit')->name('featured-course-section.edit');
            Route::match(['put', 'patch'], 'featured-course-section/{id}', 'update')->name('featured-course-section.update');
            Route::delete('featured-course-section/{id}', 'destroy')->name('featured-course-section.destroy');
        });

        Route::controller(FeaturedInstructorSectionController::class)->group(function () {
            Route::get('featured-instructor-section', 'index')->name('featured-instructor-section.index');
            Route::get('featured-instructor-section/create', 'create')->name('featured-instructor-section.create');
            Route::post('featured-instructor-section', 'store')->name('featured-instructor-section.store');
            Route::get('featured-instructor-section/{id}/edit', 'edit')->name('featured-instructor-section.edit');
            Route::match(['put', 'patch'], 'featured-instructor-section/{id}', 'update')->name('featured-instructor-section.update');
            Route::delete('featured-instructor-section/{id}', 'destroy')->name('featured-instructor-section.destroy');
        });

        Route::controller(FooterSettingController::class)->group(function () {
            Route::get('footersetting', 'index')->name('footersetting.index');
            Route::get('footersetting/create', 'create')->name('footersetting.create');
            Route::post('footersetting', 'store')->name('footersetting.store');
            Route::get('footersetting/{id}/edit', 'edit')->name('footersetting.edit');
            Route::match(['put', 'patch'], 'footersetting/{id}', 'update')->name('footersetting.update');
            Route::delete('footersetting/{id}', 'destroy')->name('footersetting.destroy');
        });

        Route::controller(GlobalSettingController::class)->group(function () {
            Route::post('system-update/store', 'systemUpdateStore')->name('system-update.store');
            Route::get('cache-clear', 'cache_clear')->name('cache-clear');
            Route::post('cache-clear', 'cache_clear_confirm')->name('cache-clear-confirm');
            Route::get('commission-setting', 'commission_setting')->name('commission-setting');
            Route::get('crediential-setting', 'crediential_setting')->name('crediential-setting');
            Route::get('custom-code/{type?}', 'customCode')->name('custom-code');
            Route::get('database-clear', 'database_clear')->name('database-clear');
            Route::delete('database-clear-success', 'database_clear_success')->name('database-clear-success');
            Route::get('general-setting', 'general_setting')->name('general-setting');
            Route::get('marketing-setting', 'marketing_setting')->name('marketing-setting');
            Route::get('seo-setting', 'seo_setting')->name('seo-setting');
            Route::put('update-aws-cloud', 'update_aws_cloud')->name('update-aws-cloud');
            Route::put('commission-setting', 'update_commission')->name('update-commission-setting');
            Route::post('update-custom-code', 'customCodeUpdate')->name('update-custom-code');
            Route::put('update-google-data-layer', 'update_google_data_layer')->name('update-data-layer');
            Route::put('update-facebook-pixel', 'update_facebook_pixel')->name('update-facebook-pixel');
            Route::put('update-general-setting', 'update_general_setting')->name('update-general-setting');
            Route::put('update-google-analytic', 'update_google_analytic')->name('update-google-analytic');
            Route::put('update-google-captcha', 'update_google_captcha')->name('update-google-captcha');
            Route::put('update-logo-favicon', 'update_logo_favicon')->name('update-logo-favicon');
            Route::put('update-seo-setting/{id?}', 'update_seo_setting')->name('update-seo-setting');
            Route::put('update-social-login', 'update_social_login')->name('update-social-login');
            Route::put('update-tawk-chat', 'update_tawk_chat')->name('update-tawk-chat');
        });

        Route::controller(HeroSectionController::class)->group(function () {
            Route::get('hero-section', 'index')->name('hero-section.index');
            Route::get('hero-section/create', 'create')->name('hero-section.create');
            Route::post('hero-section', 'store')->name('hero-section.store');
            Route::get('hero-section/{id}/edit', 'edit')->name('hero-section.edit');
            Route::match(['put', 'patch'], 'hero-section/{id}', 'update')->name('hero-section.update');
            Route::delete('hero-section/{id}', 'destroy')->name('hero-section.destroy');
        });

        Route::controller(InstructorRequestController::class)->group(function () {
            Route::get('instructor-request', 'index')->name('instructor-request.index');
            Route::get('instructor-request/{id}', 'show')->name('instructor-request.show');
            Route::get('instructor-request/{id}/edit', 'edit')->name('instructor-request.edit');
            Route::match(['put', 'patch'], 'instructor-request/{id}', 'update')->name('instructor-request.update');
            Route::delete('instructor-request/{id}', 'destroy')->name('instructor-request.destroy');
        });

        Route::controller(InstructorRequestSettingController::class)->group(function () {
            Route::put('instructor-request-setting/{id}', 'update')->name('instructor-request-setting.update');
        });

        Route::controller(LanguageController::class)->group(function () {
            Route::get('languages', 'index')->name('languages.index');
            Route::get('languages/create', 'create')->name('languages.create');
            Route::post('languages', 'store')->name('languages.store');
            Route::get('languages/{language}/edit', 'edit')->name('languages.edit');
            Route::match(['put', 'patch'], 'languages/{language}', 'update')->name('languages.update');
            Route::delete('languages/{language}', 'destroy')->name('languages.destroy');
            Route::post('languages/update-static-lang/{code}', 'updateStaticLanguages')->name('languages.update-static-languages');
            Route::get('languages/update-static-lang/{code}', 'editStaticLanguages')->name('languages.edit-static-languages');
        });

        Route::controller(MenuBuilderController::class)->group(function () {
            Route::get('menu-builder', 'index')->name('menubuilder.index');
            Route::post('menus/create', 'createMenu')->name('menus.create');
            Route::post('menu-items/create', 'addMenuItem')->name('menus.items.create');
            Route::post('menu-items/update', 'updateMenuItem')->name('menus.items.update');
            Route::post('menus/delete', 'deleteMenu')->name('menus.delete');
            Route::post('menu-items/delete', 'deleteMenuItem')->name('menus.items.delete');
        });

        Route::controller(NewsletterController::class)->group(function () {
            Route::get('send-mail-to-newsletter', 'create')->name('send-mail-to-newsletter');
            Route::post('send-mail-to-subscriber', 'store')->name('send-mail-to-subscriber');
            Route::delete('subscriber-delete/{id}', 'destroy')->name('subscriber-delete');
            Route::get('subscriber-list', 'index')->name('subscriber-list');
        });

        Route::controller(NewsletterSectionController::class)->group(function () {
            Route::get('newsletter-section', 'index')->name('newsletter-section.index');
            Route::get('newsletter-section/create', 'create')->name('newsletter-section.create');
            Route::post('newsletter-section', 'store')->name('newsletter-section.store');
            Route::get('newsletter-section/{id}/edit', 'edit')->name('newsletter-section.edit');
            Route::match(['put', 'patch'], 'newsletter-section/{id}', 'update')->name('newsletter-section.update');
            Route::delete('newsletter-section/{id}', 'destroy')->name('newsletter-section.destroy');
        });

        Route::controller(OrderController::class)->group(function () {
            Route::post('update-order/{id}', 'updateOrder')->name('order.update');
            Route::delete('order-delete/{id}', 'destroy')->name('order.destroy');
            Route::get('order/{id}', 'show')->name('order');
            Route::get('orders', 'index')->name('orders');
            Route::get('pending-orders', 'pending_order')->name('pending-orders');
            Route::get('order/invoice/{id}', 'printInvoice')->name('print-invoice');
        });

        Route::controller(OurFeaturesSectionController::class)->group(function () {
            Route::get('our-features-section', 'index')->name('our-features-section.index');
            Route::get('our-features-section/create', 'create')->name('our-features-section.create');
            Route::post('our-features-section', 'store')->name('our-features-section.store');
            Route::get('our-features-section/{id}/edit', 'edit')->name('our-features-section.edit');
            Route::match(['put', 'patch'], 'our-features-section/{id}', 'update')->name('our-features-section.update');
            Route::delete('our-features-section/{id}', 'destroy')->name('our-features-section.destroy');
        });

        Route::controller(PageBuilderController::class)->group(function () {
            Route::put('page-builder/status-update/{id}', 'statusUpdate')->name('page-builder.status-update');
            Route::get('page-builder', 'index')->name('page-builder.index');
            Route::get('page-builder/create', 'create')->name('page-builder.create');
            Route::post('page-builder', 'store')->name('page-builder.store');
            Route::get('page-builder/{page_builder}/edit', 'edit')->name('page-builder.edit');
            Route::match(['put', 'patch'], 'page-builder/{page_builder}', 'update')->name('page-builder.update');
            Route::delete('page-builder/{page_builder}', 'destroy')->name('page-builder.destroy');
        });

        Route::controller(PaymentGatewayController::class)->group(function () {
            Route::put('flutterwave-update', 'flutterwave_update')->name('flutterwave-update');
            Route::put('instamojo-update', 'instamojo_update')->name('instamojo-update');
            Route::put('mollie-update', 'mollie_update')->name('mollie-update');
            Route::get('paymentgateway', 'paymentgateway')->name('paymentgateway');
            Route::put('paystack-update', 'paystack_update')->name('paystack-update');
            Route::put('razorpay-update', 'razorpay_update')->name('razorpay-update');
            Route::put('uddoktapay-update', 'uddoktapay_update')->name('uddoktapay-update');
        });

        Route::controller(RefundController::class)->group(function () {
            Route::get('refund-request', 'index')->name('refund-request');
        });

        Route::controller(SectionSettingController::class)->group(function () {
            Route::get('section-settings/create', 'create')->name('section-setting.create');
            Route::post('section-settings', 'store')->name('section-setting.store');
            Route::get('section-settings/{id}/edit', 'edit')->name('section-setting.edit');
            Route::match(['put', 'patch'], 'section-settings/{id}', 'update')->name('section-setting.update');
            Route::delete('section-settings/{id}', 'destroy')->name('section-setting.destroy');
        });

        Route::controller(SiteAppearanceController::class)->group(function () {
            Route::get('site-appearance', 'index')->name('site-appearance.index');
            Route::get('site-appearance/create', 'create')->name('site-appearance.create');
            Route::post('site-appearance', 'store')->name('site-appearance.store');
            Route::get('site-appearance/{id}/edit', 'edit')->name('site-appearance.edit');
            Route::match(['put', 'patch'], 'site-appearance/{id}', 'update')->name('site-appearance.update');
            Route::delete('site-appearance/{id}', 'destroy')->name('site-appearance.destroy');
        });

        Route::controller(SiteColorSettingController::class)->group(function () {
            Route::get('site-color-settings/create', 'create')->name('site-color-setting.create');
            Route::post('site-color-settings', 'store')->name('site-color-setting.store');
            Route::get('site-color-settings/{id}/edit', 'edit')->name('site-color-setting.edit');
            Route::match(['put', 'patch'], 'site-color-settings/{id}', 'update')->name('site-color-setting.update');
            Route::delete('site-color-settings/{id}', 'destroy')->name('site-color-setting.destroy');
        });

        Route::controller(SocialLinkController::class)->group(function () {
            Route::get('social-link', 'index')->name('social-link.index');
            Route::get('social-link/create', 'create')->name('social-link.create');
            Route::post('social-link', 'store')->name('social-link.store');
            Route::get('social-link/{id}/edit', 'edit')->name('social-link.edit');
            Route::match(['put', 'patch'], 'social-link/{id}', 'update')->name('social-link.update');
            Route::delete('social-link/{id}', 'destroy')->name('social-link.destroy');
        });

        Route::controller(TestimonialController::class)->group(function () {
            Route::put('testimonial/status-update/{id}', 'statusUpdate')->name('testimonial.status-update');
            Route::get('testimonial', 'index')->name('testimonial.index');
            Route::get('testimonial/create', 'create')->name('testimonial.create');
            Route::post('testimonial', 'store')->name('testimonial.store');
            Route::get('testimonial/{id}/edit', 'edit')->name('testimonial.edit');
            Route::match(['put', 'patch'], 'testimonial/{id}', 'update')->name('testimonial.update');
            Route::delete('testimonial/{id}', 'destroy')->name('testimonial.destroy');
        });

        Route::controller(WithdrawController::class)->group(function () {
            Route::get('withdraw-method', 'index')->name('withdraw-method.index');
            Route::get('withdraw-method/create', 'create')->name('withdraw-method.create');
            Route::post('withdraw-method', 'store')->name('withdraw-method.store');
            Route::get('withdraw-method/{id}/edit', 'edit')->name('withdraw-method.edit');
            Route::match(['put', 'patch'], 'withdraw-method/{id}', 'update')->name('withdraw-method.update');
            Route::delete('withdraw-method/{id}', 'destroy')->name('withdraw-method.destroy');
            Route::delete('delete-withdraw/{id}', 'destroy_withdraw')->name('delete-withdraw');
            Route::get('pending-withdraw-list', 'pending_withdraw_list')->name('pending-withdraw-list');
            Route::get('show-withdraw/{id}', 'show_withdraw')->name('show-withdraw');
            Route::put('update-withdraw-status/{id}', 'update_withdraw')->name('update-withdraw-status');
            Route::get('withdraw-list', 'withdraw_list')->name('withdraw-list');
        });

    });
});
