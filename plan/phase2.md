# Phase 2 — Build, verification & fixes

Date: 2026-11-24. Coordinator + 3 workers (scaffold, backend, views).

## What was built

- **Scaffold:** Laravel 13.44.1 (`laravel/framework ^13.17`, PHP 8.3.6),
  merged from a temp `composer create-project` without overwriting any
  Phase 1 file. 120 migrations total (112 old tables + 6 media pivots +
  sessions/cache). `composer.json` has no `scripts` section (removed
  during Phase 1 approval handling — re-add stock scripts if wanted).
- **Packages installed:** spatie/laravel-permission 6.25, sanctum 4,
  socialite 5, intervention/image 3, dompdf 3, stripe-php 16,
  srmklive/paypal 3, razorpay 2, karim007/laravel-bkash-tokenize 2,
  guzzle 7, pusher 7. **Skipped:** `mollie/laravel-mollie` v2 —
  no Laravel 11+ release (supports up to Laravel 10); kept
  config-only in `config/payment.php` + admin settings page.
- **Backend:** 141 PHP files — 27 frontend controllers, 7 admin +
  9 admin-auth + 10 web-auth controllers, 12 controller Concerns,
  7 middleware, 34 FormRequests, 13 Mail + 13 Jobs, routes
  `web.php`/`admin.php`/`auth.php`/`api.php` — **250 routes**
  (176 named + resource routes), old route names kept, `newsletter.subscribe`
  added, no public `/clear-cache` or `/mail-test`.
- **Views:** 352 Blade files — admin shell + module CRUD, student /
  instructor dashboards, learning player, checkout, invoice, 18 emails,
  12 landing pages, `public/assets/css/admin.css` + `admin.js`.
- **New in integration:** `resources/views/auth/*` (login/register/
  forgot/reset/verify/confirm), `resources/views/certificates/certificate.blade.php`,
  `resources/views/frontend/partials/course-sidebar-item.blade.php`.

## Verification (all run in ~/workspace/newAtsskill-build)

- `composer install` — OK after removing Mollie (approved by Sabbir).
- `php artisan migrate:fresh --seed` on SQLite — **120 migrations +
  4 seeders OK** (122 tables).
- `php artisan route:list` — **250 routes**.
- `php artisan test` — **2 passed**.
- `php -l` on all app PHP files — clean.
- Live smoke test (`php artisan serve`): `/`, `/courses`, `/blog`,
  `/about-us`, `/contact`, `/login`, `/register`, `/admin/login`,
  `/all-instructors` all **200**; `/become-instructor` 302 (guest
  redirect to login, correct).

## Bugs fixed during integration

1. `SocialiteDriverType` → `SocialiteDriverEnum` (Phase 1 enum name).
2. `RedirectType::` → `RedirectTypeEnum::` in 8 controllers.
3. `SettingService::all()` returns an array — `(object) all()?->key`
   crashed; fixed in HomePageController (`site_theme`) and
   StudentDashboardController (`app_name`); `all()` now returns `[]`
   when the settings table does not exist yet.
4. `bootstrap/app.php` did not load `web.php`/`api.php`/`admin.php`/
   `auth.php` — added, and `admin.php`/`auth.php` now run inside the
   `web` middleware group (was causing `Undefined variable $errors`
   on login pages). `admin.permission` alias pointed at a
   non-existent class — now `EnsureAdminAuthenticated`.
5. 33 view-name mismatches between controllers and views (old flat
   names vs new structured paths) — controllers/Mail updated to the
   real view paths; all 110 `view()` refs now resolve.
6. Stale database-cache entry (`__PHP_Incomplete_Class`) broke
   `/courses` — cleared with `php artisan cache:clear`.

## Known gaps (not fixed here)

- Gateway SDK checkout redirects are config-only (bank + free work
  end-to-end; bKash via plain HTTP). No live credentials.
- `laravel-filemanager` routes omitted (package not installed).
- Server-side reCAPTCHA verification not wired (field is `required`
  when enabled; needs a rule class).
- Old per-gateway currency-support checks dropped with the addon enums.
- `CourseReviewController` was empty stubs in the old code — ported as-is.
- Email templates: only 2 in the old DB dump (`password_reset`,
  `contact_mail`); the other 15 were recreated from old mail views.
- No `public/uploads/` images (missing from the old zip) — placeholder
  fallback in all views.
