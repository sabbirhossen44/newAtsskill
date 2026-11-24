<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\BkashTokenizePaymentController;
use App\Http\Controllers\Frontend\AboutPageController;
use App\Http\Controllers\Frontend\BecomeInstructorController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckOutController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\CourseContentController;
use App\Http\Controllers\Frontend\CoursePageController;
use App\Http\Controllers\Frontend\HomePageController;
use App\Http\Controllers\Frontend\InstructorAnnouncementController;
use App\Http\Controllers\Frontend\InstructorCourseController;
use App\Http\Controllers\Frontend\InstructorDashboardController;
use App\Http\Controllers\Frontend\InstructorLessonQnaController;
use App\Http\Controllers\Frontend\InstructorLiveCredentialController;
use App\Http\Controllers\Frontend\InstructorPayoutController;
use App\Http\Controllers\Frontend\InstructorProfileSettingController;
use App\Http\Controllers\Frontend\LearningController;
use App\Http\Controllers\Frontend\NewsletterController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Frontend\QnaController;
use App\Http\Controllers\Frontend\StudentDashboardController;
use App\Http\Controllers\Frontend\StudentOrderController;
use App\Http\Controllers\Frontend\StudentProfileSettingController;
use App\Http\Controllers\Frontend\StudentReviewController;
use App\Http\Controllers\Frontend\TinymceImageUploadController;
use App\Http\Controllers\Global\CloudStorageController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — allSolution style
|--------------------------------------------------------------------------
| One Route::controller() group per controller, grouped by area
| (public / student / instructor / checkout & payment). Explicit
| routes only — no Route::resource (see plan/allsolution-style.md §4).
| URIs, methods, names and middleware are unchanged from the old code.
|
| Note: routes/admin.php and routes/auth.php are loaded by
| bootstrap/app.php (web middleware group), not required here.
*/

Route::middleware('maintenance.mode')->group(function () {

    // Public routes
    Route::controller(DashboardController::class)->group(function () {
        Route::get('set-language', 'setLanguage')->name('set-language');
    });

    Route::controller(HomePageController::class)->group(function () {
        Route::get('/', 'index')->name('home');
        Route::get('set-currency', 'setCurrency')->name('set-currency');
        Route::get('countries', 'countries')->name('countries');
        Route::get('states/{country_id}', 'states')->name('states');
        Route::get('cities/{state_id}', 'cities')->name('cities');
        Route::get('all-instructors', 'allInstructors')->name('all-instructors');
        Route::get('instructor-details/{id}/{slug?}', 'instructorDetails')->name('instructor-details');
        Route::post('quick-connect/{id}', 'quickConnect')->name('quick-connect');
        Route::get('change-theme/{name}', 'changeTheme')->name('change-theme');
    });

    // Become an instructor
    Route::controller(BecomeInstructorController::class)->group(function () {
        Route::get('become-instructor', 'index')->name('become-instructor')->middleware('auth');
        Route::post('become-instructor', 'store')->name('become-instructor.create')->middleware('auth');
    });

    // Courses
    Route::controller(CoursePageController::class)->group(function () {
        Route::get('courses', 'index')->name('courses');
        Route::get('fetch-courses', 'fetchCourses')->name('fetch-courses');
        Route::get('course/{slug}', 'show')->name('course.show');
    });

    // Cart
    Route::controller(CartController::class)->group(function () {
        Route::get('cart', 'index')->name('cart');
        Route::post('add-to-cart/{id}', 'addToCart')->name('add-to-cart');
        Route::get('buy-now/{slug}', 'buyNow')->name('buy-now');
        Route::get('remove-cart-item/{rowId}', 'removeCartItem')->name('remove-cart-item');
        Route::post('apply-coupon', 'applyCoupon')->name('apply-coupon');
        Route::get('remove-coupon', 'removeCoupon')->name('remove-coupon');
    });

    // Blog
    Route::controller(BlogController::class)->group(function () {
        Route::get('blog', 'index')->name('blogs');
        Route::get('blog/{slug}', 'show')->name('blog.show');
        Route::post('blog/submit-comment', 'submitComment')->name('blog.submit-comment');
    });

    // About page
    Route::controller(AboutPageController::class)->group(function () {
        Route::get('about-us', 'index')->name('about-us');
    });

    // Contact page
    Route::controller(ContactController::class)->group(function () {
        Route::get('contact', 'index')->name('contact.index');
        Route::post('contact/send-mail', 'sendMail')->name('contact.send-mail');
    });

    // Newsletter
    Route::controller(NewsletterController::class)->group(function () {
        Route::post('newsletter/subscribe', 'subscribe')->name('newsletter.subscribe');
        Route::get('newsletter-verification/{token}', 'verify')->name('newsletter.verification');
    });

    // Student dashboard
    Route::prefix('student')->name('student.')->middleware(['auth', 'verified'])->group(function () {
        Route::controller(StudentDashboardController::class)->group(function () {
            Route::get('dashboard', 'index')->name('dashboard');
            Route::get('enrolled-courses', 'enrolledCourses')->name('enrolled-courses');
            Route::get('quiz-attempts', 'quizAttempts')->name('quiz-attempts');
            Route::get('download-certificate/{id}', 'downloadCertificate')->name('download-certificate');
            Route::get('download-demo-certificate', 'downloadDemoCertificate')->name('download-demo-certificate');
        });

        // Profile settings
        Route::controller(StudentProfileSettingController::class)->group(function () {
            Route::get('setting', 'index')->name('setting.index');
            Route::put('setting/profile', 'updateProfile')->name('setting.profile.update');
            Route::put('setting/bio', 'updateBio')->name('setting.bio.update');
            Route::put('setting/password', 'updatePassword')->name('setting.password.update');
            Route::get('setting/experience-modal', 'showExperienceModal')->name('setting.experience-modal');
            Route::get('setting/edit-experience-modal/{id}', 'editExperienceModal')->name('setting.edit-experience-modal');
            Route::post('setting/experience', 'storeExperience')->name('setting.experience.store');
            Route::put('setting/experience/{id}', 'updateExperience')->name('setting.experience.update');
            Route::delete('setting/experience/{id}', 'destroyExperience')->name('setting.experience.destroy');
            Route::get('setting/add-education-modal', 'addEducationModal')->name('setting.add-education-modal');
            Route::post('setting/education', 'storeEducation')->name('setting.education.store');
            Route::get('setting/edit-education-modal/{id}', 'editEducationModal')->name('setting.edit-education-modal');
            Route::put('setting/education/{id}', 'updateEducation')->name('setting.education.update');
            Route::delete('setting/education/{id}', 'destroyEducation')->name('setting.education.destroy');
            Route::put('setting/address', 'updateAddress')->name('setting.address.update');
            Route::put('setting/socials', 'updateSocials')->name('setting.socials.update');
        });

        // Orders
        Route::controller(StudentOrderController::class)->group(function () {
            Route::get('orders', 'index')->name('orders.index');
            Route::get('order-details/{id}', 'show')->name('order.show');
            Route::get('order/invoice/{id}', 'printInvoice')->name('order.print-invoice');
        });

        // Reviews
        Route::controller(StudentReviewController::class)->group(function () {
            Route::get('reviews', 'index')->name('reviews.index');
            Route::get('reviews/{id}', 'show')->name('reviews.show');
            Route::delete('reviews/{id}', 'destroy')->name('reviews.destroy');
        });

        // Learning
        Route::controller(LearningController::class)->group(function () {
            Route::get('learning/{slug}', 'index')->name('learning.index');
            Route::post('learning/get-file-info', 'getFileInfo')->name('get-file-info');
            Route::post('learning/make-lesson-complete', 'makeLessonComplete')->name('make-lesson-complete');
            Route::get('learning/resource-download/{id}', 'downloadResource')->name('download-resource');
            Route::get('learning/quiz/{id}', 'quizIndex')->name('quiz.index');
            Route::post('learning/quiz/{id}', 'quizStore')->name('quiz.store');
            Route::get('learning/quiz-result/{id}/{result_id}', 'quizResult')->name('quiz.result');
            Route::get('learning/{slug}/{lesson_id}', 'liveSession')->name('learning.live');
            Route::post('add-review', 'addReview')->name('add-review');
            Route::get('fetch-reviews/{course_id}', 'fetchReviews')->name('fetch-reviews');
        });

        // Lesson Q&A
        Route::controller(QnaController::class)->group(function () {
            Route::post('create-question', 'create')->name('qna.create');
            Route::get('fetch-lesson-questions', 'fetchLessonQuestions')->name('fetch-lesson-questions');
            Route::post('create-reply', 'createReply')->name('create-reply');
            Route::get('fetch-replies', 'fetchReply')->name('fetch-replies');
            Route::delete('delete-question/{id}', 'destroyQuestion')->name('destroy-question');
            Route::delete('delete-reply/{id}', 'destroyReply')->name('destroy-reply');
        });
    });

    // Instructor dashboard
    Route::prefix('instructor')->name('instructor.')->middleware(['auth', 'verified', 'approved.instructor', 'role:instructor'])->group(function () {
        Route::controller(InstructorDashboardController::class)->group(function () {
            Route::get('dashboard', 'index')->name('dashboard');
            Route::get('my-sells', 'mySells')->name('my-sells.index');
        });

        // Live class credentials (Zoom / Jitsi)
        Route::controller(InstructorLiveCredentialController::class)->group(function () {
            Route::get('zoom-setting', 'index')->name('zoom-setting.index');
            Route::put('zoom-setting', 'update')->name('zoom-setting.update');
            Route::get('jitsi-setting', 'jitsi_index')->name('jitsi-setting.index');
            Route::put('jitsi-setting', 'jitsi_update')->name('jitsi-setting.update');
        });

        // Profile settings
        Route::controller(InstructorProfileSettingController::class)->group(function () {
            Route::get('setting', 'index')->name('setting.index');
            Route::put('setting/profile', 'updateProfile')->name('setting.profile.update');
            Route::put('setting/bio', 'updateBio')->name('setting.bio.update');
            Route::put('setting/password', 'updatePassword')->name('setting.password.update');
            Route::get('setting/experience-modal', 'showExperienceModal')->name('setting.experience-modal');
            Route::get('setting/edit-experience-modal/{id}', 'editExperienceModal')->name('setting.edit-experience-modal');
            Route::post('setting/experience', 'storeExperience')->name('setting.experience.store');
            Route::put('setting/experience/{id}', 'updateExperience')->name('setting.experience.update');
            Route::delete('setting/experience/{id}', 'destroyExperience')->name('setting.experience.destroy');
            Route::get('setting/add-education-modal', 'addEducationModal')->name('setting.add-education-modal');
            Route::post('setting/education', 'storeEducation')->name('setting.education.store');
            Route::get('setting/edit-education-modal/{id}', 'editEducationModal')->name('setting.edit-education-modal');
            Route::put('setting/education/{id}', 'updateEducation')->name('setting.education.update');
            Route::delete('setting/education/{id}', 'destroyEducation')->name('setting.education.destroy');
            Route::put('setting/payout', 'updatePayout')->name('setting.payout.update');
            Route::put('setting/address', 'updateAddress')->name('setting.address.update');
            Route::put('setting/socials', 'updateSocials')->name('setting.socials.update');
        });

        // Courses (multi-step create / edit)
        Route::controller(InstructorCourseController::class)->group(function () {
            Route::get('courses', 'index')->name('courses.index');
            Route::get('courses/create', 'create')->name('courses.create');
            Route::get('courses/create/{id}/step/{step?}', 'edit')->name('courses.edit');
            Route::get('courses/{id}/edit', 'editView')->name('courses.edit-view');
            Route::get('courses/get-filters/{category_id}', 'getFiltersByCategory')->name('courses.get-filters');
            Route::get('courses/get-instructors', 'getInstructors')->name('courses.get-instructors');
            Route::post('courses/create', 'store')->name('courses.store');
            Route::post('courses/update', 'update')->name('courses.update');
            Route::get('course-delete-request/{course_id}', 'showDeleteRequest')->name('course.delete-request.show');
            Route::post('course-delete-request', 'sendDeleteRequest')->name('course.send-delete-request');
        });

        // Course content (chapters / lessons / quiz)
        Route::controller(CourseContentController::class)->group(function () {
            Route::post('course-chapter/{course_id?}/store', 'chapterStore')->name('course-chapter.store');
            Route::get('course-chapter/sorting/{course_id}', 'chapterSorting')->name('course-chapter.sorting.index');
            Route::get('course-chapter/edit/{chapter_id}', 'chapterEdit')->name('course-chapter.edit');
            Route::put('course-chapter/update/{chapter_id}', 'chapterUpdate')->name('course-chapter.update');
            Route::delete('course-chapter/delete/{chapter_id}', 'chapterDestroy')->name('course-chapter.destroy');
            Route::post('course-chapter/sorting/{course_id}', 'chapterSortingStore')->name('course-chapter.sorting.store');
            Route::get('course-chapter/lesson/create', 'lessonCreate')->name('course-chapter.lesson.create');
            Route::post('course-chapter/lesson/create', 'lessonStore')->name('course-chapter.lesson.store');
            Route::get('course-chapter/lesson/edit', 'lessonEdit')->name('course-chapter.lesson.edit');
            Route::post('course-chapter/lesson/update', 'lessonUpdate')->name('course-chapter.lesson.update');
            Route::delete('course-chapter/lesson/{chapter_item_id}/destroy', 'chapterLessonDestroy')->name('course-chapter.lesson.destroy');
            Route::post('course-chapter/lesson/sorting/{chapter_id}', 'sortLessons')->name('course-chapter.lesson.sorting');
            Route::get('course-chapter/quiz-question/create/{quiz_id}', 'createQuizQuestion')->name('course-chapter.quiz-question.create');
            Route::post('course-chapter/quiz-question/create/{quiz_id}', 'storeQuizQuestion')->name('course-chapter.quiz-question.store');
            Route::get('course-chapter/quiz-question/edit/{question_id}', 'editQuizQuestion')->name('course-chapter quiz-question.edit');
            Route::put('course-chapter/quiz-question/update/{question_id}', 'updateQuizQuestion')->name('course-chapter.quiz-question.update');
            Route::delete('course-chapter/quiz-question/delete/{question_id}', 'destroyQuizQuestion')->name('course-chapter.quiz-question.destroy');
        });

        // Payouts
        Route::controller(InstructorPayoutController::class)->group(function () {
            Route::get('payout', 'index')->name('payout.index');
            Route::get('payout/create', 'create')->name('payout.create');
            Route::post('payout/create', 'store')->name('payout.store');
            Route::delete('payout/delete/{id}', 'destroy')->name('payout.destroy');
        });

        // Announcements
        Route::controller(InstructorAnnouncementController::class)->group(function () {
            Route::get('announcements', 'index')->name('announcements.index');
            Route::get('announcements/create', 'create')->name('announcements.create');
            Route::post('announcements', 'store')->name('announcements.store');
            Route::get('announcements/{announcement}', 'show')->name('announcements.show');
            Route::get('announcements/{announcement}/edit', 'edit')->name('announcements.edit');
            Route::match(['put', 'patch'], 'announcements/{announcement}', 'update')->name('announcements.update');
            Route::delete('announcements/{announcement}', 'destroy')->name('announcements.destroy');
        });

        // Lesson Q&A
        Route::controller(InstructorLessonQnaController::class)->group(function () {
            Route::get('lesson-question', 'index')->name('lesson-questions.index');
            Route::post('lesson-question/{id}', 'createReply')->name('lesson-question.reply');
            Route::delete('lesson-question/destroy/{id}', 'destroyQuestion')->name('lesson-question.destroy');
            Route::delete('lesson-question/reply/destroy/{id}', 'destroyReply')->name('lesson-reply.destroy');
            Route::put('lesson-question/seen-update/{id}', 'markAsReadUnread')->name('lesson-question.seen-update');
        });

        // Cloud storage
        Route::controller(CloudStorageController::class)->group(function () {
            Route::post('cloud/store', 'store')->name('cloud.store');
        });
    });

    // Checkout & payment (authenticated users)
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::controller(CheckOutController::class)->group(function () {
            Route::get('checkout', 'index')->name('checkout.index');
        });

        Route::controller(PaymentController::class)->group(function () {
            Route::get('payment', 'payment')->name('payment');
            Route::post('pay-via-stripe', 'pay_via_stripe')->name('pay-via-stripe');
            Route::get('pay-via-paypal', 'pay_via_paypal')->name('pay-via-paypal');
            Route::post('pay-via-bank', 'pay_via_bank')->name('pay-via-bank');
            Route::post('pay-via-razorpay', 'pay_via_razorpay')->name('pay-via-razorpay');
            Route::get('pay-via-mollie', 'pay_via_mollie')->name('pay-via-mollie');
            Route::get('pay-via-instamojo', 'pay_via_instamojo')->name('pay-via-instamojo');
            Route::get('pay-via-uddoktapay', 'pay_via_uddoktapay')->name('pay-via-uddoktapay');
            Route::post('pay-via-flutterwave', 'pay_via_flutterwave')->name('pay-via-flutterwave');
            Route::post('pay-via-paystack', 'pay_via_paystack')->name('pay-via-paystack');
            Route::post('pay-via-free-gateway', 'pay_via_free_gateway')->name('pay-via-free-gateway');
            Route::get('payment-addon-success', 'payment_addon_success')->name('payment-addon-success');
            Route::get('payment-addon-faild', 'payment_addon_faild')->name('payment-addon-faild');
            Route::get('order-completed', 'order_success')->name('order-success');
            Route::get('order-fail', 'order_fail')->name('order-fail');
        });

        Route::controller(TinymceImageUploadController::class)->group(function () {
            Route::post('tinymce-upload-image', 'upload');
            Route::delete('tinymce-delete-image', 'destroy');
        });
    });
});

// bKash (outside the maintenance-mode group, as in the old code)
Route::controller(BkashTokenizePaymentController::class)->group(function () {
    Route::get('bkash/payment', 'index')->name('bkash');
    Route::post('bkash/create-payment', 'createPayment')->name('bkash-create-payment');
    Route::get('bkash/callback', 'callBack')->name('bkash-callBack');
    Route::get('bkash/search/{trxID}', 'searchTnx')->name('bkash-serach');
});

// Landing pages (public)
Route::controller(LandingPageController::class)->group(function () {
    Route::get('landing-pages', 'index')->name('landing-pages.index');
    Route::get('landing-pages/create', 'create')->name('landing-pages.create');
    Route::post('landing-pages', 'store')->name('landing-pages.store');
    Route::get('landing-pages/{landing_page}', 'show')->name('landing-pages.show');
    Route::get('landing-pages/{landing_page}/edit', 'edit')->name('landing-pages.edit');
    Route::match(['put', 'patch'], 'landing-pages/{landing_page}', 'update')->name('landing-pages.update');
    Route::delete('landing-pages/{landing_page}', 'destroy')->name('landing-pages.destroy');
    Route::get('lp/{slug}', 'show')->name('landing.show');
});

Route::get('/start-right-now', function () {
    return view('landing_pages/abc_it_park');
})->name('landing.abc_it_park');

Route::get('/learn-hub', function () {
    return view('landing_pages/learnhub');
})->name('landing.learnhub');

Route::get('/atsskill-landing', function () {
    return view('landing_pages/atsskill_landing');
})->name('landing.atsskill');

Route::get('/landing/atsskill-1', function () {
    return view('landing_pages.atsskill');
})->name('landing.atsskill-1');

Route::get('/landing/atsskill-2', function () {
    return view('landing_pages.atsskill2');
})->name('landing.atsskill-2');

Route::get('/landing-checkout', function () {
    return view('landing_pages.checkout');
})->name('landing.checkout');

Route::get('/landing/atsskill-3', function () {
    return view('landing_pages.atsskill3');
})->name('landing.atsskill-3');

// Maintenance mode page
Route::get('/maintenance-mode', function () {
    if (! setting('maintenance_mode')) {
        return redirect()->route('home');
    }

    return view('global.maintenance');
})->name('maintenance.mode');

// Public custom page by slug
Route::controller(HomePageController::class)->group(function () {
    Route::get('page/{slug}', 'customPage')->name('custom-page');
});
