# Phase 2 — Scaffold (Laravel 13)

Date: 2026-11-24. Worker scope: scaffold + merge + composer manifest + config +
`.env.example` only. Did NOT touch `app/Http/`, `app/Models/`, `app/Repositories/`,
`app/Services/`, `resources/views/`, `routes/` (except stock `routes/console.php`),
the old code, or the old DB dump. All Phase 1 files were kept verbatim
(verified: 108 models, 29 enums, 18 repositories, 5 services, 118 Phase 1
migrations, 5 seeders, `config/images.php`, `app/helpers.php`,
`app/Traits/PermissionsTrait.php` all still present and unmodified).

## ⚠️ BLOCKER — `composer install` NOT run (needs Sabbir's go-ahead)

While writing this step, the scaffold's `composer.json` (stock Laravel skeleton,
copied in by the merge) was flagged by an execution-risk check: Composer
manifests can run code via `scripts` / plugins during install. Per that
flag I did **not** run `composer install`, `composer update`, `php artisan`
(any command, incl. `key:generate`, `vendor:publish`, `migrate`) or any other
code-executing step inside `~/workspace/newAtsskill-build/`.

Consequences / what is pending:

1. **No `vendor/` directory exists yet** in the build dir, and no
   `composer.lock` was generated. Nothing was installed or verified by
   running code.
2. **`composer.json` was written WITHOUT a `scripts` section.** The stock
   Laravel scripts (`setup`, `dev`, `test`, `post-autoload-dump`,
   `post-create-project-cmd`, …) were deliberately left out because they
   were part of the flagged content. After review, re-add the stock
   scripts block (from the `/tmp/laravel13-scaffold/composer.json` copy,
   which still exists) or the equivalent, then run the install.
3. **`.env` was NOT created** and no `APP_KEY` was generated (that needs
   `php artisan key:generate`). Only `.env.example` (placeholders) exists.
4. **`config/permission.php` and `config/sanctum.php` were written by hand**
   (stock Spatie v6 / Sanctum v4 contents, from knowledge) instead of via
   `artisan vendor:publish`. After install, re-publish both and diff:
   `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
   and `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`.
5. **No package compatibility was proven by a real resolve.** The
   "skipped" table below is therefore empty — no package was proven
   incompatible, because no install was attempted. The version pins in
   `composer.json` are the task's requested constraints; the first real
   `composer install` may still reject one (most likely
   `karim007/laravel-bkash-tokenize ^2`, whose last release targeted
   Laravel 10 — if it fails, record it, skip it, and wire bKash later;
   do NOT downgrade Laravel).
6. **Migration run was NOT verified** (needs `vendor/` + `.env` + key).
   The dedup below was verified statically only (every `Schema::create`
   table name is unique across all 120 migrations).

**To unblock:** Sabbir (or the parent agent, with his go-ahead) reviews this
file + `composer.json`, re-adds the scripts block, then runs in
`~/workspace/newAtsskill-build/`:
`cp .env.example .env && composer install --no-interaction && php artisan key:generate && touch database/database.sqlite && php artisan migrate --force`
then `php artisan test` (stock Example tests only, until the backend worker
adds real ones). Note: `composer create-project` already ran the same stock
skeleton scripts once inside `/tmp/laravel13-scaffold` (key generate +
migrate of the 3 stock migrations) when the scaffold was created — that
happened before the flag, in `/tmp` only, and is not part of the build dir.

## 1. Laravel version installed

- `COMPOSER_ALLOW_SUPERUSER=1 composer create-project laravel/laravel:^13.0 /tmp/laravel13-scaffold --prefer-dist --no-interaction` — succeeded.
- Scaffold reports **Laravel Framework 13.44.1**, `laravel/framework ^13.17`,
  PHP `^8.3` (runtime PHP 8.3.6, Composer 2.11.3). No downgrade; the
  `^13.0` constraint was satisfied by 13.x directly.
- The scaffold copy remains at `/tmp/laravel13-scaffold/` (with its own
  `vendor/` + generated `.env` + migrated sqlite DB — `/tmp` only, do not
  copy its `.env` or `database.sqlite` into the build; no secrets were
  copied from anywhere).

## 2. Files merged (scaffold → `~/workspace/newAtsskill-build/`)

Merged with `rsync -a --ignore-existing` (nothing already present was
overwritten). Excluded from the merge: `vendor/`, `.env`, `composer.lock`,
`app/Models/`, `app/Http/`, `resources/views/`, `routes/web.php`,
`database/migrations/` (handled separately, §3),
`database/seeders/DatabaseSeeder.php` (Phase 1 already has one),
`database/database.sqlite`, `README.md`, `AGENTS.md`, `CLAUDE.md`.

Brought in:

- `artisan`, `bootstrap/app.php`, `bootstrap/providers.php` (stock),
  `public/index.php`, `public/.htaccess`, `public/robots.txt`,
  `public/favicon.ico`, `vite.config.js`, `package.json`, `.npmrc`,
  `.gitignore`, `.gitattributes`, `.editorconfig`, `phpunit.xml`
- `config/` stock files not already present: `app.php`, `auth.php`
  (replaced by custom, §5), `cache.php`, `database.php`, `filesystems.php`
  (replaced by custom, §5), `logging.php`, `mail.php`, `queue.php`,
  `services.php` (replaced by custom, §5), `session.php`
- `routes/console.php` only (no `web.php` / `api.php` / `admin.php` —
  backend worker's)
- `database/factories/UserFactory.php` (stock; see caveat below)
- `tests/` stock (`TestCase.php`, `Feature/ExampleTest.php`,
  `Unit/ExampleTest.php`)
- `storage/` skeleton (`app/private`, `app/public`, `framework/{cache,sessions,testing,views}`, `logs`)
- `resources/css/app.css`, `resources/js/app.js` (stock Vite entry points;
  `resources/views/` untouched — stock `welcome.blade.php` NOT copied)
- `app/Providers/AppServiceProvider.php` (stock, empty — no Phase 1
  provider existed)
- `.env.example` (stock, then replaced by the full custom one, §6)

NOT brought in / not present in Laravel 13:

- `server.php` — the Laravel 13 scaffold does not ship one (removed
  upstream; `php artisan serve` is the dev server). Nothing to merge.
- `app/Http/Controllers/Controller.php`, `app/Models/User.php` — skipped
  (owned by other workers / Phase 1).
- `database/seeders/DatabaseSeeder.php` (stock) — Phase 1's file kept.

Caveats:

- `database/factories/UserFactory.php` is the stock factory (`name`,
  `email`, `password`, `remember_token`). Phase 1's `User` model/table
  has a different shape (enum `role`/`status`, etc.); the factory will
  need adapting by a later worker before it is used.
- `package.json` / `vite.config.js` are the stock Tailwind v4 + Vite 8
  scaffold. The frontend worker decides the real frontend stack
  (Bootstrap 5 per the rebuild brief); these files are only the scaffold
  baseline, and `npm install` was not run.

## 3. Migration dedup

Phase 1: 118 migrations (`2026_11_24_000001..000158`). Stock Laravel 13
ships 3 migration files; handled as follows:

| Stock file | Decision |
|---|---|
| `0001_01_01_000000_create_users_table.php` (creates `users`, `password_reset_tokens`, `sessions`) | **NOT brought in.** `users` = Phase 1 `000021`, `password_reset_tokens` = Phase 1 `000150`. |
| `0001_01_01_000001_create_cache_table.php` (creates `cache`, `cache_locks`) | **Brought in as-is.** No Phase 1 duplicate; needed for `CACHE_STORE=database`. |
| `0001_01_01_000002_create_jobs_table.php` (creates `jobs`, `failed_jobs`, `jobs_batches`) | **NOT brought in.** `jobs` = Phase 1 `000148`, `failed_jobs` = Phase 1 `000149`. Note: stock also creates `jobs_batches`; Phase 1 has no `jobs_batches` table — if batch jobs are ever used, add a migration for it then. |

New file added (sessions had no Phase 1 version):

- `2026_11_24_000159_create_sessions_table.php` — `sessions` table only,
  definition copied verbatim from the stock users migration (see the
  file's header comment). Needed for `SESSION_DRIVER=database`.

Result: **120 migrations**, and a static check confirms no
`Schema::create('…')` table name appears twice across all of them.
Phase 1 `2026_11_24_000152_create_migrations_table.php` is safe: it
returns early when the `migrations` table already exists.

## 4. `composer.json`

Merged manifest at `~/workspace/newAtsskill-build/composer.json`
(package `sabbirhossen44/newatsskill`, `laravel/framework ^13.17`,
`php ^8.3`, `laravel/tinker ^3.0`, stock `require-dev`). Autoload adds
`"files": ["app/helpers.php"]` (Phase 1 helpers, allSolution style).
**No `scripts` section — see the blocker above.**

Packages added (all as required by the task; none installed/verified yet):

| Package | Constraint |
|---|---|
| `spatie/laravel-permission` | `^6.0` |
| `laravel/sanctum` | `^4.0` |
| `laravel/socialite` | `^5.0` |
| `intervention/image` | `^3.0` |
| `dompdf/dompdf` | `^3.0` |
| `stripe/stripe-php` | `^16.0` |
| `srmklive/paypal` | `^3.0` |
| `razorpay/razorpay` | `^2.0` |
| `mollie/laravel-mollie` | `^2.0` |
| `karim007/laravel-bkash-tokenize` | `^2.0` |
| `guzzlehttp/guzzle` | `^7.8` |
| `pusher/pusher-php-server` | `^7.0` |

### Packages skipped

None — no package was skipped, because no install/resolve was attempted
(blocker above). If the first real install rejects a package, record it
here, skip it, do NOT downgrade Laravel, and leave a config-only stub
note (per the task). Prime suspect: `karim007/laravel-bkash-tokenize ^2`
(old app used `^2.3` on Laravel 10).

## 5. Config

Created / edited (all values are `env()` placeholders; no secrets):

- `config/auth.php` — **custom**: guards `web` → provider `users`
  (`App\Models\User`), `admin` → provider `admins`
  (`App\Models\Admin`, Spatie `HasRoles`, `guard_name = 'admin'`);
  providers `users` / `admins`; password brokers `users` / `admins`
  sharing the `password_reset_tokens` table (keyed by email — keep
  admin and user emails distinct in practice).
- `config/permission.php` — **custom, hand-written stock Spatie v6**:
  stock table/column names (matching Phase 1 migrations `000002–000006`),
  24 h cache. Spatie has no separate default-guard key — the guard comes
  from the model (`Admin::$guard_name = 'admin'`) and
  `auth.defaults.guard` (`web`); roles/permissions are seeded with
  `guard_name = 'admin'` (`RolePermissionSeeder`). Re-publish + diff
  after install (see blocker).
- `config/sanctum.php` — **custom, hand-written stock Sanctum v4**:
  stateful domains, `guard => ['web']`, stock middleware. Tokens table
  is Phase 1 `000151`. Re-publish + diff after install (see blocker).
- `config/services.php` — **custom**: kept stock `postmark`/`resend`/
  `ses`/`slack`; added Socialite `google` (+ `gmail` alias using the
  `GMAIL_*` names from `SettingSeeder`), `facebook`, `github`; added
  payment `stripe`/`paypal`/`razorpay`/`mollie`/`bkash` env entries.
- `config/filesystems.php` — **custom**: default disk `public`, rooted
  at `public_path('uploads/store')` with url `/uploads/store`, exactly
  as the old app's `config/filesystems.php`; added a `storage` disk
  (stock Laravel public disk, `storage/app/public`); `local` and `s3`
  unchanged.
- `config/payment.php` — **new**: gateway list `stripe`, `paypal`,
  `razorpay`, `mollie`, `instamojo`, `uddoktapay`, `flutterwave`,
  `paystack`, `bkash`, `bank`, `free`, plus live-merchant display/config
  entries `nagad`, `rocket`, `sslcommerz` (driver `null` — config only,
  no SDK wired yet; enabling one without a driver returns
  `PaymentService`'s `not_configured`). `PaymentService::GATEWAYS`
  (stripe, paypal, razorpay, flutterwave, paystack, mollie, instamojo,
  uddoktapay, bank) is the runtime source of truth; the DB
  `payment_gateways` table overrides these env defaults at runtime.
  `PAYMENT_CURRENCY` defaults to `BDT`.
- `config/app.php` — **edited**: `'timezone' => 'Asia/Dhaka'`,
  `'fallback_locale' => env('APP_FALLBACK_LOCALE', 'bn')`
  (locale stays `en`).
- Stock `config/{cache,database,logging,mail,queue,session}.php` kept
  as merged. `config/images.php` (Phase 1) untouched.

`bootstrap/app.php` — **edited**:

- `withRouting` loads only `routes/console.php` (+ `/up` health).
  `routes/web.php` / `api.php` are NOT referenced yet because those
  files do not exist; the backend worker must add
  `web: __DIR__.'/../routes/web.php'` and
  `api: __DIR__.'/../routes/api.php'` (and load `routes/admin.php`)
  when it creates them.
- Middleware aliases registered (classes to be created by the backend
  worker; aliases resolve lazily so boot is unaffected by their
  absence). Class names follow the old app's
  `app/Http/Kernel.php $middlewareAliases`, except `admin.permission`
  and `set.locale` which are new:
  - `'role'` → `App\Http\Middleware\Role`
  - `'approved.instructor'` → `App\Http\Middleware\ApprovedInstructorMiddleware`
  - `'maintenance.mode'` → `App\Http\Middleware\MaintenanceModeMiddleware`
  - `'translation'` → `App\Http\Middleware\SetLocaleMiddleware`
    (same as the old alias)
  - `'set.locale'` → `App\Http\Middleware\SetLocaleMiddleware`
  - `'admin.permission'` → `App\Http\Middleware\AdminPermission`
    (new class, no old equivalent — backend worker to create)
- JSON exception rendering for `api/*` kept from stock.

## 6. `.env.example`

Full placeholder file written (no real values anywhere):

- `APP_NAME="ATS Skill"`, `APP_LOCALE=en`, `APP_FALLBACK_LOCALE=bn`
- `DB_CONNECTION=sqlite` default for local verification; MySQL block
  commented out (`DB_DATABASE=atsskill` placeholder)
- `SESSION_DRIVER=database`, `CACHE_STORE=database`,
  `QUEUE_CONNECTION=database`, `FILESYSTEM_DISK=public`
- Mail (`MAIL_*`, log driver), AWS/S3, Vite placeholders
- Pusher (`PUSHER_APP_*`), Socialite (`GOOGLE_*`, `GMAIL_*`,
  `FACEBOOK_APP_*`, `GITHUB_*`), all payment gateways
  (`STRIPE_*`, `PAYPAL_*`, `RAZORPAY_*`, `MOLLIE_*`, `INSTAMOJO_*`,
  `UDDOKTAPAY_*`, `FLUTTERWAVE_*`, `PAYSTACK_*`, `BKASH_*`,
  `NAGAD_*`, `ROCKET_*`, `SSLCOMMERZ_*`), Zoom/Jitsi placeholders
  (per-instructor credentials live in DB tables, not env),
  reCAPTCHA, Tag Manager, Pixel, Tawk, logo/favicon placeholders
  (key names match `SettingSeeder` where they overlap)
- **No `.env` was created** and no key generated (blocked — see top).
  Local verify steps once unblocked:
  `cp .env.example .env && php artisan key:generate && touch database/database.sqlite && php artisan migrate --force`

## 7. What could not be done

1. `composer install` / lockfile / `vendor/` — blocked (see top).
2. `.env` + `APP_KEY` generation — blocked (needs Artisan).
3. `artisan vendor:publish` for Spatie/Sanctum configs — hand-written
   instead; re-publish + diff after install.
4. `php artisan migrate` / `php artisan test` verification — blocked
   (needs `vendor/` + `.env`); migration correctness checked statically
   only (unique table names, guarded `000152`).
5. `routes/web.php`, `routes/api.php`, `routes/admin.php` — not created
   (backend worker's); `bootstrap/app.php` must be updated when they land.
6. Middleware classes (`App\Http\Middleware\*`) — not created
   (backend worker's); only aliases registered.
7. `npm install` / `npm run build` — not run; frontend stack is the
   frontend worker's decision.
8. No secrets were copied from the old `.env` (never read for values);
   the old code was only consulted for names (middleware aliases,
   filesystem root, gateway list, old Composer constraints).
