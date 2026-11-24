# allSolution code-style guide (style reference for the ATS Skill rebuild)

Source: `CSN-Bangladesh/allSolution`, branch `dev`, read 2026-11-24
(via GitHub read-only tools; HEAD ref at fetch time `df4d10f4…`).
No secrets are included in this file — env keys are named only, never valued.

> Scope: this is a **style** reference only. Domain code (marketplace items,
> OTP, social login, chat, bookings) is NOT to be copied into ATS Skill;
> only the patterns below are reused, re-skinned for LMS entities
> (courses, lessons, students, instructors, enrolments, etc.).

## 1. Versions — exact

| Stack | Version in allSolution |
|---|---|
| PHP | `^8.3` |
| Laravel | `^13.8` (`laravel/framework`) |
| `arafatdev/laravel-repository` | `*` |
| `intervention/image` | `^4.0` |
| `laravel/sanctum` | `^4.0` |
| `laravel/socialite` | `*` |
| `socialiteproviders/apple` | `*` |
| `spatie/laravel-permission` | `*` |
| `laravel/tinker` | `^3.0` |
| `monitorhub/client` | `*` (their own monitoring client — do NOT reuse unless Sabbir says so) |

Dev: `fakerphp/faker ^1.23`, `fruitcake/laravel-debugbar *`, `laravel/boost ^2.2`,
`laravel/pail ^1.2.5`, `laravel/pao ^1.0.6`, `laravel/pint ^1.27`,
`mockery/mockery ^1.6`, `nunomaduro/collision ^8.6`, `pestphp/pest ^4.0`,
`pestphp/pest-plugin-laravel ^4.0`.

Frontend (`package.json`): Vite `^8.0.0`, `laravel-vite-plugin ^3.1`,
`@tailwindcss/vite ^4.0.0`, `tailwindcss ^4.0.0`, `@vitejs/plugin-react ^6.0.5`,
React `^19.2.8` (+ Redux Toolkit, React Query, TipTap, etc.) and
`bootstrap ^5.3.8` (devDependency). **The admin panel itself does NOT use
the npm Bootstrap — it loads vendored copies from `public/assets/vendor/`
(see §9).** `README.md` is the stock Laravel README — no project docs.

`composer.json` scripts worth copying: `setup` (install → copy `.env.example`
→ `key:generate` → `migrate --force` → `npm install` → `npm run build`);
`dev` runs `serve` + `queue:listen` + `pail` + `npm run dev` concurrently.

`.env.example` shape (keys only): `APP_*`, `DB_CONNECTION=mysql`,
`DB_DATABASE=allsolution`, `SESSION_DRIVER=database`, `SESSION_LIFETIME=120`,
`CACHE_STORE=database`, `QUEUE_CONNECTION=database`,
`FILESYSTEM_DISK=public`, `BROADCAST_CONNECTION=log`,
`MAIL_MAILER=smtp` (host `mail.csnmailbox.com`), `MEDIA_CDN_ENABLED`,
`MEDIA_CDN_URL`, `MEDIA_CDN_KEY`, `SOCKET_URL`, `VITE_API_BASE_URL`,
`VITE_SOCKET_URL`. No `.env` values are reproduced here.

## 2. Layered structure

```
app/
  Enums/            string-backed PHP enums (see §5)
  Helpers — app/helpers.php, autoloaded via composer.json "files"
  Http/
    Controllers/Controller.php   abstract base (API json() envelope only)
    Controllers/Admin/*          Blade admin controllers
    Controllers/Api/*            Sanctum API controllers
    Middleware/EnsureAdminAuthenticated.php   (only custom middleware)
    Requests/Admin/*             admin FormRequests
    Requests/*                   API FormRequests
    Resources/*                  API resources (ProductResource, *ListResource…)
  Models/           one model per table; $guarded = ['id'] (except User, §6)
  Repositories/      API-side repositories (Request-based signatures)
  Repositories/Admin/  admin repositories (validated-array signatures)
  Services/         ImageService.php only
bootstrap/app.php   Laravel 13 style — NO app/Http/Kernel.php exists
config/auth.php, config/services.php, config/images.php
routes/admin.php  routes/web.php  routes/api.php  routes/console.php
resources/views/  Blade admin (layouts/, per-resource dirs) + app.blade.php (SPA shell)
database/migrations/  anonymous-class migrations, 48 files
database/seeders/     RoleSeeder, AdminSeeder, SettingSeeder, …
```

Rules of the layering:

- **Controllers are thin.** Index/show/edit do querying + `view()`; store/update
  delegate to a repository; status/bulk/destroy are small and inline.
- **Repositories hold write logic** (`storeByRequest` / `updateByRequest`) and
  lookups (`findBySlug`, `findByContact`). Controllers never call
  `Model::create()` directly for a write that a repository owns.
- **Two parallel repository namespaces** with *different* signatures for the
  same entity — keep this split in the rebuild:
  `App\Repositories\ProductRepository::updateByRequest(Request $request, Product $product)`
  vs `App\Repositories\Admin\ProductRepository::updateByRequest(Product $product, array $data)`.
  Admin repositories take the already-validated array; API repositories take
  the whole `Request`.
- **Services are rare** — only `ImageService`. Cross-cutting helpers live in
  `app/helpers.php`, not in a service class.

## 3. Repository pattern (`arafatdev/laravel-repository`)

There is **no `BaseRepository` in `app/`** — the base class is the vendor
package `Arafat\LaravelRepository\Repository`. Every repository:

```php
use Arafat\LaravelRepository\Repository;

class ProductRepository extends Repository
{
    public static function model()          // required, static, no return type
    {
        return Product::class;
    }
    // … static methods only; never instantiated with `new`
}
```

Base methods actually used (observed across the codebase — `self::create`
×9, `self::query` ×7, `self::update` ×6 in the files sampled):

| Call | Meaning |
|---|---|
| `self::model()` | returns the model class string |
| `self::create([...])` | create + return model |
| `self::update($model, [...])` | update, returns `bool` |
| `self::query()` | Eloquent query builder for the model |

Method-naming conventions (all `public static`, `*ByRequest` suffix):

- `storeByRequest(Request $request)` / admin: `storeByRequest(array $data, …)`
- `updateByRequest(Request $request, Model $model)` / admin: `updateByRequest(Model $model, array $data)`
- `findBySlug(string $slug): ?Model`, `findById(int $id): ?Model`,
  `findByContact(string $content): ?User`
- Media only: `storeByRequest(UploadedFile, string $path, ?string $type, ?string $folder, ?string $description)`,
  `updateByRequest(UploadedFile, string $path, ?string $type, Media $media, ?string $folder)`,
  `updateOrCrateByRequest(...)` — note the verbatim typo **`updateOrCrate…`**
  (missing "e"); keep the spelling in the rebuild or fix it everywhere at once.
  `deleteByMedia(Media $media): bool`, `optimizeExisting(Media $media, bool $force = false): bool`,
  `storeByFromUrlRequest(string $url, string $path, ?string $description = null): ?Media`
- Pivot-media repositories: `storeByRequest(Model $parent, array $medias): void`,
  `updateByRequest(Model $parent, ?array $medias, ?array $existingMedias): void`,
  `deleteByProductId(int $id): void`
- Small helpers: `updateCategoryUse(Category $category)`, `updateLastLogin(User $user)`

Other style notes: docblocks are sparse and sometimes stale (e.g. a
`/** base method @method model() */` comment above `model()`); commented-out
code is left in place (e.g. an old `findByContact` in `UserRepository`);
`// dd($request->all());` appears in `Admin\UserRepository`. Rebuild: keep
the naming, drop the dead code.

## 4. Naming conventions

- Models singular PascalCase (`Product`, `ProductMedia`, `ProProfile`);
  tables snake plural; pivot tables `{item}_media` (`product_media`,
  `service_media`, `apartment_media`, `work_media`, `portfolio_media`).
- Controllers `XxxController` in `Admin` / `Api` namespaces; admin views are
  `resources/views/{plural}/` (`products/`, `users/`, `admins/`, …) and view
  names are referenced **without** an `admin.` prefix: `view('products.index')`.
- Routes: explicit `Route::controller(XController::class)->group(...)` with
  named routes `admin.products.index` etc. **No `Route::resource`** — the
  resource lines are commented out in `routes/admin.php`. Per-resource verbs:
  `GET index`, `GET {x}/show`, `GET {x}/edit`, `PUT {x}` update,
  `PATCH {x}/status` updateStatus, `DELETE {x}` destroy,
  `POST {x}/bulk` bulk. Some resources use `create`/`store` with
  `/category/create` + `POST /category/store` (singular segment) instead of
  RESTful `/categories/create`.
- FormRequests: `App\Http\Requests\Admin\ProductRequest`, `AdminUserRequest`,
  `UserUpdateRequest` (API side, in `Requests\` root).
- Enums: `{Thing}StatusEnum`, `{Thing}PriceTypeEnum`; values are
  lowercase snake (`'active'`, `'sold_out'`).
- `perPage()`: every admin controller defines its **own private**
  `perPage(Request $request): int` — default `15`, whitelist `[10, 25, 50, 100]`
  (the abstract base `Controller` does NOT provide it).

## 5. Enums — 29 files in `app/Enums/`

All are plain string-backed enums, one case per line, no methods, no
`label()` helpers:

`ApartmentPriceTypeEnum`, `ApartmentStatusEnum`, `BookingStatusEnum`,
`BoostStatusEnum`, `CategoryTypeEnum`, `ConditionTypeEnum`,
`ConversationStatusEnum`, `HiringStatusEnum`, `ItemTypeEnum`,
`MessageTypeEnum`, `NotificationTypeEnum`, `OfferStatusEnum`,
`OrderStatusEnum`, `OtpPurposeEnum`, `OtpSendTypeEnum`, `PriceTypeEnum`,
`ProPriceTypeEnum`, `ProProfilePriceTypeEnum`, `ProProfileStatusEnum`,
`ProductPriceTypeEnum`, `ProductStatusEnum`, `ReportStatusEnum`,
`ReviewStatusEnum`, `RoleEnum`, `ServicePriceTypeEnum`,
`ServiceStatusEnum`, `UserStatusEnums`, `WorkPriceTypeEnum`,
`WorkStatusEnum`.

```php
enum RoleEnum: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case BUYER = 'buyer';
    case SELLER = 'seller';
    case PROVIDER = 'provider';
}
```

```php
enum ProductStatusEnum: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case REJECTED = 'rejected';
    case SOLD_OUT = 'sold_out';
}
```

Usage pattern — always `->value` when persisting/comparing, `::cases()` for
dropdowns, `Rule::in(array_column(XxxStatusEnum::cases(), 'value'))` for
validation (see §8). Quirks to **not** copy: `UserStatusEnums` is plural
while every other enum is singular `…Enum`; `OtpPurposeEnum::LOGIN_VERIFY`
has the value `'login verify'` (space, not snake_case). For the rebuild use
singular `…Enum` names and snake_case values throughout
(e.g. `CourseStatusEnum`, `EnrolmentStatusEnum`, `LessonTypeEnum`).

`NotificationTypeEnum` is a good template for a "type registry" enum —
16 cases (`offer`, `hiring`, `service`, `work`, `apartment`, `product`,
`pro_profile`, `message`, `report`, `boost`, `hiring_inquiry`,
`product_order`, `service_booking`, `work_booking`, `apartment_order`, `system`).

## 6. Models & the User model

- Most models: `protected $guarded = ['id'];`, `use SoftDeletes;`, plain
  relation methods, `boot()` for slug/username generation.
- Slug pattern (Product, Category, …): `boot()` sets the slug on `creating`
  and regenerates it on `updating`; `generateUniqueSlug($title, $acceptId = null)`
  appends `-1`, `-2`, … while `self::withTrashed()->where('slug', $slug)->where('id', '!=', $acceptId)->exists()`.
- `User` is the exception — it uses PHP attributes instead of properties:

```php
#[Fillable(['name', 'user_name', 'email', 'password', 'media_id', 'email_verified_at', 'phone', 'phone_verified_at', 'city_id', 'area_id', 'status', 'last_login_at', 'provider', 'provider_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }
    // …
}
```

- `User` extras: `getContactAttribute()` (`email ?? phone`); relations
  `media()` (`belongsTo(Media::class)` via `media_id`), `city()`, `area()`,
  `products()`, `services()`, `works()`, `apartments()`, `proProfile()`
  (`hasOne()->latestOfMany()`), `favorites()`, `reports()`,
  `receivedReviews()`/`sentReviews()` with optional `when()` filters.
- Username generation in `boot()`: `creating` → `generateUniqueUsername()`;
  `updating` (only when `name`/`email` dirty) → regenerate. Candidates in
  order: first-name slug, full-name slug, email-local-part slug, then
  first-name + 1–5 random digits; uniqueness checked with `withTrashed()`.
- `thumbnail()` accessor (User, Product, Category, …) — always an
  `Illuminate\Database\Eloquent\Casts\Attribute` returning
  `Media::resolveUrl(...)` with an `asset(...)` fallback:

```php
public function thumbnail(): Attribute
{
    $media = $this->primaryMedia?->media;

    return Attribute::make(
        get: fn () => Media::resolveUrl($media?->url) ?? asset('images/default.webp'),
    );
}
```

(User falls back to `asset('images/avatar.jpg')`.)

Users table (`2026_08_06_122332_create_users_table`): `name` nullable,
`user_name` unique nullable, `email` unique nullable, `phone` nullable,
`email_verified_at` / `phone_verified_at`, `password` nullable,
`status` default `UserStatusEnums::ACTIVE->value`, `media_id` /
`country_id` / `city_id` / `area_id` nullable FKs (`nullOnDelete`),
`last_login_at`, `rememberToken`, soft deletes, indexes on `name`, `user_name`.
Spatie permission tables are the stock `2026_08_06_121217_create_permission_tables`
migration.

## 7. Media pattern — central `media` table + per-item pivots

`media` table (`2026_08_05_121653_create_media_table`):

```php
Schema::create('media', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('url');            // storage path OR absolute (CDN) URL
    $table->string('type')->nullable();       // 'image' or extension
    $table->string('extension')->nullable();
    $table->string('description')->nullable();
    $table->softDeletes();
    $table->timestamps();
});
```

- `App\Models\Media`: only `$guarded = ['id']`, no relations, no traits.
  URL resolution is a **static** method (verbatim, 22 lines):

```php
public static function resolveUrl(?string $value): ?string
{
    if (blank($value)) {
        return null;
    }

    if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
        return $value;
    }

    $disks = array_unique(array_filter(['public', config('filesystems.default')]));

    foreach ($disks as $disk) {
        try {
            if (Storage::disk($disk)->exists($value)) {
                return Storage::disk($disk)->url($value);
            }
        } catch (\Throwable $e) {
            continue;
        }
    }

    return null;
}
```

- Single-avatar case: the owner model holds `media_id` (`users.media_id`)
  and a `media()` `belongsTo(Media::class)`; upload goes through
  `MediaRepository::updateOrCrateByRequest($file, 'users', 'image', $media)`.
- Gallery case: a pivot model per item type, e.g. `ProductMedia`
  (`product_id`, `media_id`, `order` int default 1, `is_primary` bool
  default false; both FKs `cascadeOnDelete`). The item model exposes:

```php
public function primaryMedia()
{
    return $this->hasOne(ProductMedia::class)->where('is_primary', true);
}
```

  plus a `getMediasAttribute()` that returns the pivot rows ordered by
  `order`, mapped to `['media_id' => $pivot->id, 'is_primary', 'url' => $pivot->thumbnail, 'name', 'extension']`.
  `ProductMedia::thumbnail()` resolves its own URL (absolute URL passthrough,
  else `Storage::url()`), falling back to `asset('images/default.webp')`.
- `ProductMediaRepository::storeByRequest($product, $medias)` stores each
  `['file', 'is_primary']` via `MediaRepository::storeByRequest($file, 'products', null, 'products')`
  with `order = $key + 1`. `updateByRequest()` upserts by pivot `media_id`,
  appends new rows at `max(order) + 1`, and deletes pivot rows not in
  `$existingMedias`.
- `ImageService` (`app/Services/ImageService.php`): `MAX_DIMENSION = 1280`,
  `WEBP_QUALITY = 80`, `SUPPORTED_EXTENSIONS = ['jpg','jpeg','png','gif','webp']`;
  `optimize(UploadedFile)` returns `[$upload, $optimizedPath|null]` — a temp
  WebP (scaled to max 1280 longest side, quality 80) via the
  `Illuminate\Support\Facades\Image` facade (`Image::fromUpload($file)->scale(width: $max, height: $max)->toWebp()->quality($quality)->toBytes()`).
  Non-supported files pass through untouched; the caller (`MediaRepository`)
  unlinks the temp file in a `finally`. Config: `config/images.php`
  (`IMAGE_DRIVER` default `gd`, `IMAGE_MAX_DIMENSION` 1280, `IMAGE_QUALITY` 80).
- `MediaRepository` storage branches on environment: **local → `Storage::disk('public')`**;
  otherwise a custom CDN (`config/services.php` `media_cdn`: `enabled` / `url` / `key`
  from `MEDIA_CDN_*` env; upload via `Http::attach('file', …)->post(url, ['folder' => …])`
  with `X-API-Key` header; delete via a `/delete` endpoint). `isLocal()` is
  just `app()->environment('local')`. Type is auto-detected: image extensions
  → `'image'`, otherwise the extension string.
- For the rebuild: keep the central `media` table + `resolveUrl()` +
  `ImageService` + per-item pivot pattern exactly; the CDN branch can be
  dropped (or kept behind the same `media_cdn` config) — ATS Skill has no CDN.

## 8. Settings helper & `Setting` model

`app/helpers.php` is autoloaded via `composer.json` `"files": ["app/helpers.php"]`.
All helpers are wrapped in `if (! function_exists('…'))`.

```php
if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever('site_settings', function () {
            return Setting::all()->pluck('value', 'key')->map(fn($value) => is_string($value) ? $value : (string) $value);
        });

        return $settings->get($key, $default);
    }
}
```

`setSettingValue($key, $value, $group = 'general', $isActive = true)` does
`Setting::updateOrCreate(['key' => $key], ['value' => …, 'group' => $group, 'is_active' => $isActive])`
(JSON-encoding non-string values). `Setting` model: `$guarded = ['id']`,
`is_active` cast to boolean, and `booted()` forgets the `site_settings`
cache key on `saved` and `deleted`.

`settings` table: `key` unique string, `value` text nullable, `group`
nullable string (indexed), `is_active` bool default true, timestamps.

Other helpers in the same file (marketplace-specific — do NOT copy the
domain, copy the shape): `getItemModel(string $type): string`,
`getItem($type, $id)`, `findOrFailItem($type, $id)`,
`getItemResource($type, $itemId)`, `getItemListResource($type): string`,
`getItemActiveStatus($type): string` — all `match ($type)` over the five
item-type strings (`product`, `apartment`, `service`, `work`, `pro_profile`)
throwing `InvalidArgumentException` on default; and `getEquivalentPhones()`
(Bangladesh `01…` ↔ `+88…` ↔ `88…`, plus Italy `3…` ↔ `+39…`).

## 9. Admin auth pattern

`config/auth.php`: **two session guards, one provider/model** —

```php
'guards' => [
    'web'   => ['driver' => 'session', 'provider' => 'users'],
    'admin' => ['driver' => 'session', 'provider' => 'users'],
],
'providers' => ['users' => ['driver' => 'eloquent', 'model' => env('AUTH_MODEL', User::class)]],
```

So an "admin" is a `User` row with the Spatie `admin` role, logged in on the
separate `admin` guard (separate session from the frontend `web` guard).

`bootstrap/app.php` registers the route + middleware alias:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->prefix('admin')->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => App\Http\Middleware\EnsureAdminAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        // + a PostTooLargeException handler returning a clean 413 JSON payload
    })->create();
```

`EnsureAdminAuthenticated::handle()` — verbatim core (role + status checks;
JSON branch returns `{success:false, message}` with 401/403, web branch
redirects to `admin.login` with a flash `error`):

```php
public function handle(Request $request, Closure $next): Response
{
    /** @var \App\Models\User|null $user */
    $user = auth('admin')->user();

    if (! $user || ! $user instanceof User || ! $user->hasRole(RoleEnum::ADMIN->value)) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return redirect()
            ->route('admin.login')
            ->with('error', 'Please log in to access the admin panel.');
    }

    if ($user->status !== 'active') {
        auth('admin')->logout();
        // … 403 JSON, or redirect to admin.login with
        // 'This admin account is inactive.'
    }

    return $next($request);
}
```

Admin login (`Admin\AuthController@login`): validate `email` + `password`;
find the user with `User::query()->where('email', …)->role(RoleEnum::ADMIN->value)->first()`
(Spatie `role()` scope); then `auth('admin')->validate($credentials)`; reject
inactive accounts; `auth('admin')->login($user, $request->boolean('remember'))`,
`$request->session()->regenerate()`, stamp `last_login_at = now()`,
`redirect()->intended(route('admin.dashboard'))`. Logout: `auth('admin')->logout()`,
invalidate session, regenerate token. Login routes: `GET /admin/login`
(`guest:admin`), `POST /admin/login` with `throttle:5,1`.

Admins are managed as users: `AdminController@index` queries
`User::query()->role(RoleEnum::ADMIN->value)`; `AdminUserRepository::storeByRequest(array $data, bool $status)`
creates the user then `$user->assignRole(RoleEnum::ADMIN->value)`; update
re-assigns the role if missing. Self-protection: `updateStatus`/`destroy`
refuse when `$admin->id === auth('admin')->id()`.

Roles are seeded from the enum (`RoleSeeder`): `RoleEnum::cases()` →
`Role::query()->updateOrCreate(['name' => $role, 'guard_name' => 'web'])`.
`AdminSeeder` creates/updates a default admin user and assigns the admin role
(credentials intentionally not reproduced here).

## 10. Admin controller + FormRequest style

`App\Http\Controllers\Admin\ProductController` is the template
(index / show / edit / update / updateStatus / destroy / bulk):

- `index(Request $request)`: `$query = Product::query()->with(['user','category','city','area'])`;
  `search` filter (title LIKE + `orWhereHas('user')` on name/email);
  `status` / `category` filters ignored when value is `'all'`;
  `$query->latest()->paginate($this->perPage($request))->withQueryString()`;
  enum cases passed to the view for filter dropdowns:
  `$statuses = ProductStatusEnum::cases();`
  `return view('products.index', compact('products','statuses','categories'))`.
- `show` / `edit`: route-model binding (`Product $product`); `edit` eager-loads
  relations and passes `statuses`, `categories`, `cities` (`where('is_active', true)`).
- `update(ProductRequest $request, Product $product)`: one line —
  `ProductRepository::updateByRequest($product, $request->validated());`
  then `redirect()->route('admin.products.show', $product)->with('success', 'Product updated successfully.')`.
- `updateStatus(Request $request, Product $product)`: inline `$request->validate(['status' => ['required', Rule::in(array_column(ProductStatusEnum::cases(), 'value'))]])`,
  `$product->update(['status' => …])`; returns JSON `{success, message}` when
  `$request->expectsJson()`, otherwise `back()->with('success', …)`.
- `destroy`: `$product->trashed() ? $product->forceDelete() : $product->delete();`
  (soft-delete first, force-delete when already trashed) → redirect to index
  with `success`.
- `bulk(Request $request)`: validates `action` in `['activate','deactivate','delete']`
  + `ids` array `min:1`; loads `Product::withTrashed()->whereIn('id', …)->get()`;
  `match ($validated['action']) { … }` for the three actions; `back()->with('success', 'Bulk action completed successfully.')`.
- Flash wording is always `'<Thing> <verb> successfully.'` / `'Bulk action completed successfully.'`;
  errors are `'No products selected.'`, `'You cannot deactivate your own account.'`, etc.

`App\Http\Requests\Admin\ProductRequest` is the template FormRequest:

```php
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // auth is enforced by the admin.auth middleware, not here
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'condition' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_column(ProductStatusEnum::cases(), 'value'))],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'price.required' => 'The price field is required.',
            'price.numeric' => 'The price must be a number.',
        ];
    }
}
```

Validation idioms: array-style rules everywhere; `Rule::in(array_column(Enum::cases(), 'value'))`
for enum fields; `Rule::unique('users','email')->ignore($admin?->id)->whereNull('deleted_at')`
(soft-delete-aware uniqueness, see `AdminUserRequest`); conditional rules
built from `$this->route('admin')` (password `required` on create, `nullable`+`confirmed`
on update, `min:8`); custom `messages()` only for the fields worth a
friendlier sentence. API-side requests (e.g. `UserUpdateRequest`) add inline
closure rules and `auth('sanctum')->user()`-aware logic, plus image rules
`['nullable','image','max:2048','mimes:jpeg,jpg,png,gif,svg,webp']`.

API base controller (`App\Http\Controllers\Controller`, abstract): a single
`json(?string $message, mixed $data, int $statusCode, array $errors, array $headers, array $extra): JsonResponse`
helper producing `{success, message?, data?, errors?, meta?}` with pagination
meta `{current_page, per_page, total, last_page}` lifted out of paginated
resources, `JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES`.

Routes (`routes/admin.php`, 223 lines, ~150 route definitions): wrapped in
`Route::name('admin.')->group(...)`; guest login group, then one
`Route::middleware('admin.auth')->group(...)` containing per-resource
`Route::controller(…)`: users, professionals, products, apartments, services,
works, categories, cities/areas, transactions (orders/bookings/hiring),
conversations/offers, reviews, reports, contacts, faqs, legal-pages, teams,
settings (`GET /settings` + `POST /settings`), social-auths. `->withTrashed()`
on show/edit/update/status/destroy routes for soft-deletable resources.
`routes/api.php` (248 lines): OTP auth (`request-otp`/`verify-otp`), social
auth redirect/callback, `master`, `contact/store`, home/category/city,
per-item `index` + `{slug}/show` + `similar-*`, all under `/api` with Sanctum.

## 11. Blade admin layout

`resources/views/layouts/app.blade.php` is the **admin** layout (6.8 KB).
The public frontend is a separate SPA shell, `resources/views/app.blade.php`
(see §12 — do not copy it).

Layout skeleton: `<!DOCTYPE html>` → `<head>` (Inter font from Google Fonts,
`setting('site_favicon', '/images/favIcon.svg')`, `<meta name="csrf-token">`,
`noindex, nofollow`, `theme-color #4f46e5`, title
`{{ config('app.name') }} — Admin · @yield('title', 'Dashboard')`) →
`<body class="admin-body">` with an inline pre-paint script that reads
`localStorage` (`admin.sidebar-collapsed`, `admin.theme`) and sets
`data-bs-theme` on `<html>` → `@include('layouts.partials.sidebar')` →
`.admin-main` with `@include('layouts.partials.header')`,
`<main class="admin-content"><div class="container-fluid">@yield('content')</div></main>`,
`@include('layouts.partials.footer')` → hidden `#deleteForm` (POST + `@method('DELETE')`)
and `#logoutForm` (POST to `admin.logout`) → `@include('layouts.partials.confirm-modal')`,
`@include('partials.image-viewer')` → scripts → `@stack('scripts')` →
SweetAlert2 toast mixin + `session('success'|'error'|'warning'|'info')` toasts →
jQuery handlers for `.deleteConfirm` (SweetAlert confirm → submit `#deleteForm`)
and `.logoutButton`.

Assets are **local vendored files**, not CDN/npm (Bootstrap 5):

```html
<link href="{{ asset('assets/vendor/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/icons/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/admin.css') }}?v={{ @filemtime(public_path('assets/css/admin.css')) ?: time() }}" rel="stylesheet">
<script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/js/admin.js') }}"></script>
```

Plus Select2 CSS with the Bootstrap 5 theme. `@stack('styles')` / `@stack('scripts')`
for per-page assets. Partials: `layouts/partials/{sidebar, header, footer, confirm-modal, alerts}.blade.php`
(`alerts` exists but is commented out in the layout — toasts are used instead).

Views tree (`resources/views/`): `admins/`, `apartments/`, `auth/` (login),
`categories/`, `cities/`, `components/`, `contacts/`, `conversations/`,
`dashboard.blade.php` (29 KB, single-file dashboard), `faqs/`,
`layouts/` (+ `layouts/partials/`), `legal-pages/`, `mail/`,
`notifications/`, `offers/`, `partials/` (incl. `image-viewer`),
`products/`, `professionals/`, `reports/`, `reviews/`, `services/`,
`settings/`, `social-auth/`, `teams/`, `transactions/`, `users/`,
`vendor/`, `works/`, plus root `app.blade.php` (SPA shell).

## 12. Patterns that do NOT fit the ATS Skill rebuild

1. **React SPA frontend — do NOT copy.** `routes/web.php` is a single
   catch-all `Route::view('/{any}', 'app')->where('any', '(?!admin($|/)).*')`;
   `resources/views/app.blade.php` is an SEO-heavy SPA shell loading
   `resources/js/App.jsx` via Vite with React 19, Redux Toolkit, React
   Query, TipTap, Tailwind 4. The rebuild uses **Blade for the public
   frontend** instead — keep only the admin Blade layout (§11) and build
   public pages as Blade views.
2. **Single `users` table with Spatie roles for admins — do NOT copy.**
   allSolution has no `admins` table: admins are `users` with role `admin`,
   the `admin` guard points at the same `users` provider, and
   `EnsureAdminAuthenticated` checks `hasRole('admin')`. Per Sabbir's
   decision the rebuild **keeps a separate `admins` table** (ATS Skill
   legacy): the guard/provider, middleware, `AuthController`, `AdminController`
   and `AdminUserRepository` must be re-pointed at an `Admin` model/table,
   and Spatie roles stay on the LMS side (instructors/students) or per the
   rebuild's role-permission plan — do not reuse `RoleEnum::ADMIN` as the
   admin identity.
3. **Marketplace domain — do NOT copy.** The five generic item types
   (`product`, `apartment`, `service`, `work`, `pro_profile`), their
   `*StatusEnum` / `*PriceTypeEnum` enums, `getItem*` helpers, OTP login,
   social login (Socialite + Apple provider), conversations/offers, bookings,
   orders, boosts, hiring, reviews/reports, `monitorhub/client`,
   `SOCKET_URL` / socket.io, and the CDN media branch are allSolution-domain
   specifics. Reuse the *shapes* (enum per status, pivot media per entity,
   `*ByRequest` repositories) with LMS entities.
4. **CDN media uploads — adapt, don't copy verbatim.** `MediaRepository`
   branches on `app()->environment('local')` (public disk) vs CDN upload
   with an API key. The rebuild has no CDN: use the local branch only
   (`Storage::disk('public')`), keep `resolveUrl()` (it already handles
   both stored paths and absolute URLs).
5. **Tailwind 4 + React build chain — do NOT copy for the admin.**
   `vite.config.js` builds `resources/css/app.css` + `resources/js/App.jsx`
   with the Tailwind Vite plugin. The admin panel needs no Vite build at
   all (vendored Bootstrap/jQuery/Select2/SweetAlert2 under `public/assets/`);
   the rebuild's Blade frontend should follow the same vendored-asset
   approach (Bootstrap 5, minimal custom CSS) per Sabbir's rebuild brief.
6. **Minor inconsistencies to fix rather than replicate:** the
   `updateOrCrateByRequest` typo; `UserStatusEnums` (plural) vs singular
   enum names; `OtpPurposeEnum::LOGIN_VERIFY = 'login verify'` (space in
   value); API vs Admin repositories having different `updateByRequest`
   argument orders; dead commented-out code and `// dd(…)` left in
   repositories; `alerts` partial existing but unused (toasts instead).

## 13. Composer `require` list to reuse in the rebuild

Copy verbatim (versions as in allSolution `dev`):

```json
"require": {
    "php": "^8.3",
    "arafatdev/laravel-repository": "*",
    "intervention/image": "^4.0",
    "laravel/framework": "^13.8",
    "laravel/sanctum": "^4.0",
    "laravel/socialite": "*",
    "laravel/tinker": "^3.0",
    "spatie/laravel-permission": "*"
}
```

- **Keep:** `arafatdev/laravel-repository`, `intervention/image`,
  `laravel/sanctum`, `spatie/laravel-permission`, `laravel/tinker`.
- **Keep only if the LMS needs it:** `laravel/socialite` (+
  `socialiteproviders/apple`) — only for social login; ATS Skill legacy
  has no social login, so default to **omit**.
- **Omit:** `monitorhub/client` (vendor-specific monitoring).
- Dev (keep): `laravel/pint`, `pestphp/pest` + `pest-plugin-laravel`,
  `laravel/boost`, `fruitcake/laravel-debugbar`; omit `laravel/pail` /
  `laravel/pao` unless wanted.
- Frontend: the rebuild is Blade + Bootstrap 5, so the React/Tailwind
  dependency set is **not** reused; keep `bootstrap ^5.3.8` (vendored into
  `public/assets/vendor/` as in allSolution, or via npm) plus
  `laravel-vite-plugin` only if a Vite build is wanted at all.
