# ATS Skill — Laravel 13 Rebuild

ATS Skill is an online skill-development / LMS academy, rebuilt from the
original Laravel 10 (SkillGro v1.5.0) codebase into a fresh **Laravel 13 /
PHP 8.3** project, following the code style of the
[allSolution](https://github.com/CSN-Bangladesh/allSolution) project:
layered app (Models / Repositories / Services / Enums / FormRequests),
central `media` table, Bootstrap 5 Blade admin + frontend, Spatie
permissions. **Business logic, routes, permission names and the database
schema are the same as the old site.**

Live site: https://www.atsskill.com/

## Stack

- Laravel 13.44, PHP ^8.3, Vite
- Spatie Permission 6, Sanctum 4, Socialite 5, Intervention Image 3
- Payments (config-only until credentials are added): Stripe, PayPal,
  Razorpay, bKash (tokenize), Instamojo, UddoktaPay, Flutterwave,
  Paystack, bank, free — plus Nagad / Rocket / SSLCOMMERZ as
  display/config entries (live footer merchants)
- Frontend: Blade + Bootstrap 5.3, theme-one homepage
- Admin: Blade + Bootstrap 5.3

## Identity (kept from the old site)

- `users` table — students and instructors (`role` = instructor/student)
- `admins` table — admins, guard `admin`, Spatie `HasRoles`
  (roles: Super Admin, Manager — 105 permissions, same names as old)
- `instructors` table — instructor profiles

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite   # or configure MySQL in .env
php artisan migrate:fresh --seed
php artisan serve
```

Seeders: `RolePermissionSeeder` (105 permissions), `SettingSeeder`,
`LanguageSeeder` (en/bn), `EmailTemplateSeeder`.

## Structure

- `app/Models/` — 108 models (incl. 16 translation models)
- `app/Repositories/` — 18 static repositories
- `app/Services/` — ImageService, SettingService, PaymentService,
  CertificateService, TranslationService
- `app/Enums/` — 29 enums
- `app/Http/Controllers/` — Frontend, Admin (+ Auth), Auth, Global
- `app/Http/Requests/` — Frontend + Admin FormRequests
- `routes/` — `web.php`, `admin.php`, `auth.php`, `api.php` (stub)
- `resources/views/frontend/` — theme-one homepage, pages, dashboards
- `resources/views/admin/` — admin panel (Bootstrap 5)
- `database/migrations/` — 120 migrations (112 old tables + 6 media
  pivots + sessions/cache)
- `plan/` — style guide, DB schema, Phase 2 notes

## Notes / still missing

- `public/uploads/` images were not in the old project zip — views fall
  back to `public/images/placeholder.webp`. Copy the live uploads in.
- No real credentials are in this repo (`.env.example` placeholders
  only). Add payment / mail / Zoom / Jitsi / Pusher / reCAPTCHA /
  social-login keys via `.env` on the server.
- Mollie (`mollie/laravel-mollie` v2) has no Laravel 13 release, so it
  is config-only; the old Mollie settings page is kept.
- Gateway SDK checkout redirects (Stripe/PayPal/Razorpay/…) are wired
  config-only; bank + free gateways work end-to-end, bKash uses plain
  HTTP tokenize calls.
