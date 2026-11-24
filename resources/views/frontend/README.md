# Frontend views — theme-one (Blade)

Public Blade frontend for the ATS Skill Laravel 13 rebuild. Admin layout is
separate (see `plan/allsolution-style.md` §11); the public site is **Blade,
not React**.

## Structure

```
resources/views/frontend/
  layouts/  master, header, footer, styles, scripts
  home-one/ index + sections/ (hero, categories, courses, newsletter,
            instructors, features, become, testimonials, stats,
            admission, team, blog)
  partials/ course-card, blog-card, breadcrumb, modal
  pages/    courses/{index,show}, blog/{index,show}, about, contact,
            instructors/{index,show}, become-instructor, custom-page,
            cart/index, checkout/index, order-success, order-fail
  student-dashboard/    layouts/{master,sidebar}, index
  instructor-dashboard/ layouts/{master,sidebar}, index
public/assets/css/frontend.css   small theme overrides (Bootstrap 5 does the rest)
public/assets/js/frontend.js     mobile nav is Bootstrap offcanvas; Swiper
                                  inits + add-to-cart fetch, all guarded
```

## Homepage order (live site, theme-one)

Hero → Top Category → Popular Courses → Newsletter → Expert Instructors →
"Start Your Learning Journey Today!" (4 tiles) → Become Instructor/Student →
Testimonials → Stats strip → Admission banner → Expert Team Members →
Latest News Feed. Footer: royal blue, Munshiganj map + address, Useful
Links, Get In Touch, payment banner (bKash / Nagad / Rocket / SSLCOMMERZ —
display only, no secrets).

## Conventions the controllers must honour

- `setting($key, $default)` helper (allsolution-style §8) for site info:
  `app_name`, `logo`, `favicon`, `version`, `mail_sender_email`,
  `copyright_text`. No `$setting` object is used anywhere.
- Images: `\App\Models\Media::resolveUrl($path)` with fallback
  `asset('images/placeholder.svg')`. Legacy varchar image columns
  (`courses.thumbnail`, `users.image`, `blogs.image`, …) are passed through
  `resolveUrl()` so stored paths, absolute URLs, and missing files all work.
  The old `public/uploads` images were missing from the zip — the placeholder
  covers every missing file.
- Prices are rendered directly as `৳{{ number_format($price, 0) }}` (BDT).
  No `currency()` helper is assumed; if one is added later it can replace
  these spots.
- Strings use `__('…')` so en/bn language files can translate them.
- Translation relations follow the old schema: `$model->translation->name`
  (current-language row), guarded with `??` everywhere.
- Breadcrumb is a plain partial (`frontend.partials.breadcrumb`), not the
  old `<x-frontend.breadcrumb>` component — no component class needed.

## Variables each view expects (all optional-guarded with `??`)

| View | Variables |
|---|---|
| `home-one.index` | `$seo_setting['home_page']`, `$sectionSettings` (section_settings row), `$hero`, `$categories`, `$courses`, `$courseTabs` (optional `[{id,name,courses}]`), `$newsletterSection`, `$instructors`, `$featuredInstructorSection`, `$ourFeatures`, `$bannerSection`, `$testimonials`, `$counter`, `$teamMembers`, `$blogs` |
| `pages.courses.index` | `$courses` (paginated), `$categories`, `$levels`, `$languages` |
| `pages.courses.show` | `$course` (with category, instructor, chapters→items/lessons, reviews, `avg_rating`, `enrollments_count`), `$relatedCourses` |
| `pages.blog.index/show` | `$blogs` / `$blog`, `$blogCategories`, `$relatedBlogs`, `$blogComments` |
| `pages.about` | `$about`, `$counter`, `$teamMembers` |
| `pages.contact` | `$contact` (contact_sections) |
| `pages.instructors.*` | `$instructors` / `$instructor` (+ courses, educations, experiences), `$badges` (optional) |
| `pages.cart.index` | `$cartItems` (`rowId,id,name,slug,price,options.image`), `$cartSubtotal`, `$cartDiscount`, `$cartTotal` |
| `pages.checkout.index` | `$cartItems`, `$cartTotal`, `$cartDiscount`, `$payableAmount` |
| dashboards | `$stats`, `$recentEnrollments` / `$recentOrders` |

Shared by header/footer (view composer recommended): `$cartCount`,
`$footerSettings`, `$socialLinks`, `$navMenu`, `$footerMenuOne/Two/Three`.

## Route names used (from old routes/web.php — must exist)

`home`, `set-language`, `courses`, `fetch-courses` (not used by views yet),
`course.show`, `cart`, `add-to-cart`, `buy-now`, `remove-cart-item`,
`apply-coupon`, `remove-coupon`, `blogs`, `blog.show`, `blog.submit-comment`,
`all-instructors`, `instructor-details`, `quick-connect`, `about-us`,
`contact.index`, `contact.send-mail`, `become-instructor`,
`become-instructor.create`, `custom-page`, `login`, `register`, `logout`,
`checkout.index`, `payment`, `pay-via-stripe`, `pay-via-paypal`,
`pay-via-bank`, `pay-via-razorpay`, `pay-via-mollie`, `pay-via-instamojo`,
`pay-via-uddoktapay`, `pay-via-flutterwave`, `pay-via-paystack`,
`pay-via-free-gateway`, `bkash`, `bkash-create-payment`, `order-success`,
`order-fail`, `student.*`, `instructor.*` (see dashboard sidebars).

**To confirm with the routes agent:** `newsletter.subscribe` (newsletter
form posts here — no such route existed in the old web.php excerpt) and the
`custom-page` slugs used as fallbacks (`our-services`, `privacy-policy`,
`terms-conditions`, `refund-policy`) must exist as `custom_pages` rows.

## Assets still needed (not vendored, not copied)

Vendored CSS/JS the layout references under `public/assets/vendor/`:
Bootstrap 5.3 (`bootstrap/css/bootstrap.min.css`,
`bootstrap/js/bootstrap.bundle.min.js`), jQuery (`jquery/jquery.min.js`),
Swiper (`swiper/swiper-bundle.min.css/.js`), Slick (`slick/slick.css/.js`),
Font Awesome (`fontawesome/css/all.min.css`). The old theme's `main.css`
(20k lines) and `frontend.css` (7.5k lines) were **not** copied — the new
`public/assets/css/frontend.css` is a small fresh override file; visual
parity with the old theme depends on re-checking against the live site once
vendor CSS is in place.

Images still missing (old `public/uploads` + `public/frontend/img` were not
in the zip / not copied): `public/images/placeholder.svg`,
`public/images/logo.svg`, `public/images/favicon.png`,
`public/images/footer/payment-methods.jpg`,
`public/images/admission/student.png` (+ `bg.jpg`),
`public/images/footer/image.jpg` (old single payment image). All views fall
back to the placeholder where an image is missing, so nothing breaks — the
pages just look plain until brand art is re-supplied.

## What is skeleton vs complete

- **Complete:** layouts, all 12 homepage sections, course card / blog card /
  breadcrumb, courses list + details, blog list + details, about, contact,
  instructors list + details, become-instructor, custom page, cart, order
  success/fail.
- **Skeleton (structure + real routes, logic deferred to controllers):**
  checkout (payment-method cards post to the old gateway routes; no gateway
  JS/SDK is embedded), student dashboard index, instructor dashboard index.
  Dashboard sub-pages (orders, enrolled courses, reviews, quiz attempts,
  profile settings, course builder, lesson Q&A, payout, announcements,
  my sells, Zoom/Jitsi settings) are **not** built — only their sidebar
  links and the dashboard shells exist.
- **Not reproduced from the old theme:** the old inline `<style>` hacks in
  `header.blade.php` (mobile cart/auth overrides), the preloader, Google Tag
  Manager / Tawk / reCAPTCHA / cookie-consent scripts, the learning player,
  and the old `main.css`/`frontend.css` look — replaced by clean Bootstrap 5
  (offcanvas mobile menu) + the small `frontend.css`.

## Phase 2 additions (2026-11-24)

- **Admin views** — `resources/views/admin/` (200 files): `master_layout` (Bootstrap
  5.3 CDN + Font Awesome + Inter, `data-bs-theme` light/dark toggle), `sidebar`
  (desktop + offcanvas, `@can` with the old permission names), `partials/`
  (styles, javascripts, modal incl. delete-confirm + dynamic modal,
  paginate_box), `dashboard` (stat cards + recent orders/courses), `auth/`
  (app/login/forgot-password/reset-password), `admin-list/`, `roles/` (+
  assign-role), `instructors/`, `team-members/`, `profile/edit_profile`,
  `settings/` (settings + sidebar), plus per-module CRUD under
  `admin/<module>/` (course, course-category/sub-category/level/language,
  course-review, course-delete-request, blogs, blog-category/comment, badges,
  brand, faq, testimonial, coupon, page-builder, menubuilder, languages,
  country, currency, certificate-builder, newsletter, contact-message,
  footersetting, social-link, order, withdraw + withdraw-method,
  instructor-request (+ setting), customer, refund — stub only, see below).
  Global settings, payment-gateway, basic-payment, frontend sections
  (hero/about/featured-*/newsletter/counter/our-features/banner/faq/contact)
  and site-appearance/section-setting/site-color-setting live under
  `admin/global-setting/`, `admin/payment-gateway/`, `admin/basic-payment/`,
  `admin/<section>/`, `admin/site-appearance/` etc.
- **Student dashboard complete**: orders (index/show/invoice), enrolled-courses,
  quiz-attempts, reviews (index/show), profile settings (profile/bio/password/
  experience/education/address/socials + modals), certificate page, and the
  **learning player** (`pages/learning-player/`): lesson rendering per
  `LessonStorageEnum` (upload video / YouTube / Vimeo / Google Drive / iframe /
  external link / live Zoom-Jitsi), chapter/lesson accordion, progress bar,
  mark-as-complete, resource download, Q&A tab, reviews tab, quiz index/result.
- **Instructor dashboard complete**: courses (index, 4-step create/edit wizard
  with navigation/more-information/course-content/finish + chapter/lesson/quiz/
  document/live modals), announcements, payouts, my-sells, lesson-qna,
  zoom/jitsi settings, profile settings incl. payout, order list.
- **Checkout finished**: coupon apply/remove (`apply-coupon` / `remove-coupon`)
  added to the order summary; gateway cards post to the old route names.
  Print invoice at `frontend/pages/invoice.blade.php` (used by both the
  student order invoice and `admin/order/invoice.blade.php`).
- **Emails**: `resources/views/emails/` (18) incl. payment_reject_mail,
  contact_mail, newsletter verify, instructor-request approved/rejected/pending.
- **Landing pages**: `frontend/landing_pages/` (atsskill, atsskill2, atsskill3,
  learnhub, checkout, atsskill_landing, abc_it_park, custom, show + admin
  index/create/edit) reusing home-one sections. Public resource routes are
  `landing-pages.*` (old web.php) plus `landing.show`, `landing.abc_it_park`,
  `landing.learnhub`, `landing.atsskill`.
- **Assets**: `public/assets/css/admin.css`, `public/assets/js/admin.js`
  (theme toggle, delete-modal wiring, status submits),
  `public/assets/vendor/README.md` + placeholder dirs — vendor CSS/JS stays on
  CDN, nothing large was downloaded.

## Known gaps / route mismatches found in Phase 2

- `newsletter.subscribe` has no route in the old web.php (already noted above);
  the newsletter form still posts there — routes agent must add it.
- `admin.role.*` comes from `Route::resource('/role', ...)` in old admin.php
  (leading slash, no `->names()`); Laravel default names apply.
- Refund is a **stub only** (index + show with a disabled-module notice) per the
  task; the old admin refund routes are `admin.refund-request`,
  `admin.pending-refund-request`, `admin.show-refund-request`, etc.
- Location admin covers countries only — the old Location module had no
  state/city resource routes, so no `admin/city/` views were created.
- Admin views use `@can('<old permission>')` as specified; the permissions must
  exist as gates/abilities (Spatie-style names like `course.management`) for
  the checks to pass — verify in the auth/permissions setup.
- Admin course create/edit is a single form (not the 4-step wizard — the wizard
  lives on the instructor side); admin course content editing reuses the
  instructor course-content partials via `admin.courses.edit` step links.
