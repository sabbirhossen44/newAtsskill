# allSolution Style Spec v2 — verbatim (CSN-Bangladesh/allSolution, ref df4d10f4c57ef20316e82ed7b4c23b0811b30724)

Read verbatim via `github call-read-tool get_file_contents` (no branch param in the tool; the tool resolved this SHA). This spec replaces guesses from summaries. Where the rebuild must differ (ATS Skill domain: `admins` table + `admin` guard, old route names/URIs, old view paths, `media.src` column, no CDN), the difference is marked **DEVIATION** with the reason.

## 1. Base controller — `app/Http/Controllers/Controller.php` (verbatim, abridged)

```php
abstract class Controller
{
    protected function json(?string $message = null, mixed $data = null, int $statusCode = Response::HTTP_OK, array $errors = [], array $headers = [], array $extra = []): JsonResponse
    {
        $content = ['success' => $statusCode >= 200 && $statusCode < 300];
        if ($message) { $content['message'] = $message; }
        if ($data !== null && $data !== []) { $content['data'] = $this->resolveData($data); }
        if ($errors) { $content['errors'] = $errors; }
        if ($meta = $this->extractPaginationMeta($data)) { $content['meta'] = $meta; }
        foreach ($extra as $key => $value) { if (! array_key_exists($key, $content)) { $content[$key] = $value; } }
        return response()->json($content, $statusCode, $headers, JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES);
    }
    // resolveData() unwraps paginated ResourceCollections; extractPaginationMeta() returns current_page/per_page/total/last_page
}
```

**Pattern:** the base controller has ONLY the `json()` helper. **There is NO `perPage()` on the base controller.** Every admin controller defines its own private `perPage()` (see §2). **DEVIATION in our build (to fix):** our base `Controller` currently holds `protected perPage()` — remove it; put a private `perPage()` in each controller that lists, exactly as below.

## 2. Admin controller pattern — verbatim

### `AdminController` (app/Http/Controllers/Admin/AdminController.php)

```php
class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->role(RoleEnum::ADMIN->value);

        if ($request->filled('search')) {
            $search = trim($request->string('search'));
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return view('admins.index', [
            'admins' => $query->latest()->paginate($this->perPage($request))->withQueryString(),
            'filters' => $request->only(['search', 'status', 'per_page']),
        ]);
    }

    public function create()
    {
        return view('admins.create');
    }

    public function store(AdminUserRequest $request)
    {
        AdminUserRepository::storeByRequest($request->validated(), $request->boolean('status', true));

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin created successfully.');
    }

    public function edit(User $admin)
    {
        return view('admins.edit', ['adminUser' => $admin]);
    }

    public function update(AdminUserRequest $request, User $admin)
    {
        AdminUserRepository::updateByRequest($admin, $request->validated(), $request->boolean('status'));

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    public function updateStatus(Request $request, User $admin)
    {
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($request->has('status')) {
            $admin->update(['status' => $request->boolean('status') ? 'active' : 'inactive']);
        } else {
            $admin->update(['status' => $admin->status === 'active' ? 'inactive' : 'active']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin status updated successfully.',
            ]);
        }

        return back()->with('success', 'Admin status updated successfully.');
    }

    public function destroy(User $admin)
    {
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
```

**Rules extracted (verbatim):**
- `index(Request $request)`: build `$query = Model::query()` (+ `->role(...)` / `->with([...])` as needed), `if ($request->filled('search'))` with `trim($request->string('search'))` and `like "%{$search}%"`, `if ($request->filled('status') && $request->status !== 'all')` then `->where('status', $request->status)`, extra filters the same way (`type`, `category`). Return `view(...)` with `->latest()->paginate($this->perPage($request))->withQueryString()` and `'filters' => $request->only(['search', 'status', 'per_page'])` (plus any extra filter keys used).
- `store(FormRequest $request)`: `Repository::storeByRequest($request->validated(), $request->boolean('status', true))`, then `redirect()->route('...index')->with('success', 'X created successfully.')`. Some controllers use `to_route('...')->withSuccess('...')` — either spelling is verbatim allSolution; prefer `redirect()->route()->with('success', ...)` for consistency.
- `update(FormRequest $request, Model $x)`: `Repository::updateByRequest($x, $request->validated(), ...)` then redirect with `'X updated successfully.'`.
- `updateStatus(Request $request, Model $x)`: self-check first (`if ($x->id === auth('admin')->id()) return back()->with('error', 'You cannot deactivate your own account.')` for admins; for other resources validate status with `Rule::in(array_column(StatusEnum::cases(), 'value'))`); toggle or set from `$request->boolean('status')`; `if ($request->expectsJson()) return response()->json(['success' => true, 'message' => 'X status updated successfully.']);` else `back()->with('success', ...)`.
- `destroy(Model $x)`: self-check for admins; `$x->trashed() ? $x->forceDelete() : $x->delete();` for soft-deletable models; redirect with `'X deleted successfully.'`.
- `bulk(Request $request)`: validate `action` (`Rule::in(['activate','deactivate','delete'])`) + `ids` (`required|array|min:1`, `ids.*` integer); `Model::withTrashed()->whereIn('id', $validated['ids'])->get()`; empty → `back()->with('error', 'No X selected.')`; `match ($validated['action']) { 'activate' => ...->update(['status' => StatusEnum::ACTIVE->value]), 'deactivate' => ..., 'delete' => $items->each(fn($p) => $p->trashed() ? $p->forceDelete() : $p->delete()), };` then `back()->with('success', 'Bulk action completed successfully.')`.
- `private function perPage(Request $request): int` in EVERY listing controller: `(int) $request->input('per_page', 15)`, whitelist `[10, 25, 50, 100]` else `15`.
- **No traits, no RedirectTypeEnum, no `checkAdminPermission()` in controllers, no `abort_if($x->id == 1, 403)`.** Permission is enforced by the `admin.auth` middleware + Spatie roles, not per-method helper calls. **DEVIATION (ATS domain, keep):** our admin routes/views/permissions use the old Spatie permission names (`admin.view` etc.) and the old view paths (`admin.admin-list.index`, route `admin.admin.index`). Keep those names/paths — only the code shape changes. Self-check uses `auth('admin')->id()` (admin guard), not a hardcoded id 1.

### `ApartmentController` — extra verbs (verbatim)

```php
public function show(Apartment $apartment)
{
    $statuses = ApartmentStatusEnum::cases();
    return view('apartments.show', compact('apartment', 'statuses'));
}
public function edit(Apartment $apartment)
{
    return view('apartments.edit', [
        'apartment' => $apartment->load(['user', 'category', 'city', 'area']),
        'statuses' => ApartmentStatusEnum::cases(),
        'categories' => Category::where('type', 'apartment')->orderBy('name')->get(),
        'cities' => City::where('is_active', true)->orderBy('name')->get(),
    ]);
}
```

Enum cases are passed to views as `$statuses = XStatusEnum::cases();` / `$types = XTypeEnum::cases();` and used in `compact(...)`.

### `CategoryController` — singular create/store (verbatim)

```php
public function store(CategoryRequest $request)
{
    CategoryRepository::storeByRequest($request);
    return to_route('admin.categories.index')->withSuccess('Category created successfully.');
}
public function update(CategoryRequest $request, Category $category)
{
    CategoryRepository::updateByRequest($request, $category);
    return to_route('admin.categories.index')->withSuccess('Category updated successfully.');
}
```

Note: CategoryRepository takes the whole `Request`, not `validated()` — see §3. Inconsistent signatures are verbatim allSolution; the rewrite keeps each repository's own signature and makes its controller call it the same way.

### `AreaController` — nested resource + toggle (verbatim)

```php
public function index(Request $request, City $city)
{
    if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['success' => true, 'data' => $city->areas()->where('is_active', true)->orderBy('name')->get(['id', 'name'])]);
    }
    return to_route('admin.cities.edit', compact('city'));
}
public function store(AreaRequest $request, City $city)
{
    AreaRepository::storeByRequest($request, $city);
    return back()->withSuccess('Area created successfully.');
}
public function toggle(Request $request, Area $area)
{
    if ($request->has('status')) { $area->update(['is_active' => $request->boolean('status')]); }
    else { $area->update(['is_active' => ! $area->is_active]); }
    if ($request->expectsJson()) { return response()->json(['success' => true, 'message' => 'Area status updated successfully.']); }
    return back()->with('success', 'Area status updated successfully.');
}
```

### `AuthController` (admin login, verbatim)

```php
public function showLoginForm() { return view('auth.login'); }
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);
    $user = User::query()->where('email', $credentials['email'])->role(RoleEnum::ADMIN->value)->first();
    if (! $user || ! auth('admin')->validate($credentials)) {
        return back()->withInput($request->only('email'))->withErrors(['email' => 'These credentials do not match our records.']);
    }
    if ($user->status !== 'active') {
        return back()->withInput($request->only('email'))->withErrors(['email' => 'This admin account is inactive.']);
    }
    auth('admin')->login($user, $request->boolean('remember'));
    $request->session()->regenerate();
    $user->update(['last_login_at' => now()]);
    return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back, '.$user->name.'.');
}
public function logout(Request $request)
{
    auth('admin')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
}
```

Note: status is compared as a plain string (`$user->status !== 'active'`) — **no enum cast on the model** (see §5). **DEVIATION (ATS domain):** our admin identity is the separate `Admin` model + `admins` table + `admin` guard (not `User` + `role(ADMIN)`). Keep that; the shape (validate → find → validate credentials → status check → login → regenerate → `last_login_at`) is copied exactly, with `Admin` in place of `User`.

## 3. Repositories — verbatim (all extend `Arafat\LaravelRepository\Repository`)

### `AdminUserRepository`

```php
class AdminUserRepository extends Repository
{
    public static function model() { return User::class; }

    public static function storeByRequest(array $data, bool $status): User
    {
        $user = self::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'status' => $status ? 'active' : 'inactive',
        ]);
        $user->assignRole(RoleEnum::ADMIN->value);
        return $user;
    }

    public static function updateByRequest(User $user, array $data, bool $status): bool
    {
        $payload = ['name' => $data['name'], 'email' => $data['email'], 'status' => $status ? 'active' : 'inactive'];
        if (! empty($data['password'])) { $payload['password'] = $data['password']; }
        $result = self::update($user, $payload);
        if ($result && ! $user->hasRole(RoleEnum::ADMIN->value)) { $user->assignRole(RoleEnum::ADMIN->value); }
        return $result;
    }
}
```

### `ApartmentRepository`

```php
public static function updateByRequest(Apartment $apartment, array $data): bool
{
    return self::update($apartment, [
        'title' => $data['title'],
        'description' => $data['description'] ?? null,
        'price' => $data['price'],
        'condition' => $data['condition'] ?? null,
        'status' => $data['status'],
        'category_id' => $data['category_id'] ?? null,
        'city_id' => $data['city_id'] ?? null,
        'area_id' => $data['area_id'] ?? null,
    ]);
}
```

### `CategoryRepository`

```php
public static function storeByRequest(Request $request): Category
{
    return self::create([
        'name' => $request->name,
        'type' => $request->type,
        'status' => (bool) $request->status,
    ]);
}
public static function updateByRequest(Request $request, Category $category)
{
    return $category->update([
        'name' => $request->name,
        'type' => $request->type,
        'status' => (bool) $request->status,
    ]);
}
```

**Signature reality (verbatim, inconsistent in allSolution itself — do not "fix" it into one shape):**
- `AdminUserRepository`: `storeByRequest(array $data, bool $status)` / `updateByRequest(User $user, array $data, bool $status)` — data first on store, model first on update.
- `ApartmentRepository`: `updateByRequest(Apartment $apartment, array $data)` — model first, data second, explicit field list with `?? null` defaults.
- `CategoryRepository`: `storeByRequest(Request $request)` / `updateByRequest(Request $request, Category $category)` — whole `Request`, request first.
- `MediaRepository`: `storeByRequest(UploadedFile $file, string $path, ?string $type = null, ?string $folder = null, ?string $description = null)` / `updateByRequest(UploadedFile $file, string $path, ?string $type, Media $media, ?string $folder = null)` — file first, media 4th.
- Repositories use `self::create([...])` / `self::update($model, [...])` from the base `Arafat\LaravelRepository\Repository` and a `public static function model()` returning the model class.
- **DEVIATION (build reality):** our repositories are plain static classes (no `arafatdev/laravel-repository` package in the build). Keep the plain static shape and the `model()` method; implement `create`/`update` via the model query directly. Do NOT add the package in this pass — behaviour must not change. Record this as a known deviation.

## 4. Media — verbatim structure, LOCAL ONLY (Sabbir 2026-11-25: no CDN)

### `MediaRepository` (verbatim shape; CDN branches REMOVED)

```php
class MediaRepository extends Repository
{
    public static function model() { return Media::class; }

    public static function storeByRequest(
        UploadedFile $file, string $path, ?string $type = null, ?string $folder = null, ?string $description = null
    ): Media {
        $originalName = $file->getClientOriginalName();
        [$upload, $optimizedPath] = app(ImageService::class)->optimize($file);
        try {
            // LOCAL ONLY: Storage::disk('public')->put('/'.trim($path, '/'), $upload);
            $extension = $upload->extension();
            if (! $type) {
                $type = in_array(strtolower((string) $extension), ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp']) ? 'image' : $extension;
            }
            return self::create([
                'type' => $type,
                'url' => $url,
                'name' => $originalName,
                'extension' => $upload->extension(),
                'description' => $description ?? null,
            ]);
        } finally {
            if ($optimizedPath) { @unlink($optimizedPath); }
        }
    }

    public static function storeByFromUrlRequest(string $url, string $path, ?string $description = null): ?Media
    {
        try {
            $response = Http::timeout(15)->get($url);
            if (! $response->successful()) { return null; }
            $tempPath = sys_get_temp_dir().'/'.Str::uuid().'.jpg';
            file_put_contents($tempPath, $response->body());
            $file = new UploadedFile($tempPath, 'google-avatar.jpg', 'image/jpeg', null, true);
            $media = self::storeByRequest($file, $path, 'image', 'profiles', $description);
            @unlink($tempPath);
            return $media;
        } catch (\Exception $e) { return null; }
    }

    public static function updateByRequest(UploadedFile $file, string $path, ?string $type, Media $media, ?string $folder = null): Media
    {
        // same optimize + local put as store; $media->update([...]); self::removeFile($oldUrl); return $media;
    }

    // NOTE: verbatim allSolution name has a typo: updateOrCrateByRequest. Our build keeps the correctly-spelled
    // updateOrCreateByRequest (DEVIATION, deliberate — same signature/behaviour, correct spelling).
    public static function updateOrCreateByRequest(UploadedFile $file, string $path, ?string $type = null, ?Media $media = null, ?string $folder = null, ?string $description = null): Media
    {
        if ($media) { return self::updateByRequest($file, $path, $type, $media, $folder); }
        return self::storeByRequest($file, $path, $type, $folder, $description);
    }

    public static function deleteByMedia(Media $media): bool
    {
        self::removeFile((string) $media->url);
        return (bool) $media->delete();
    }
}
```

**Local-only rules (Sabbir):** NO `MEDIA_CDN_URL`, no `services.media_cdn` config, no `uploadToCdn`/`cdnPath`/`cdnDeleteUrl`/`useCdn`/`assertCdnConfigured`, no remote disk. `isLocal()` branching is removed — the local `public`-disk branch is the ONLY branch. `removeFile()` deletes from the `public` disk only. **DEVIATION (ATS domain):** our `media` table column is `src` (old DB), not `url` — keep `src` in our code, same shape otherwise. Existing views keep working via `Media::resolveUrl()` (see §5) with placeholder fallback where uploads are missing.

### `ImageService` (verbatim shape)

```php
class ImageService
{
    public const MAX_DIMENSION = 1280;
    public const WEBP_QUALITY = 80;
    public const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function maxDimension(): int { return (int) config('images.max_dimension', self::MAX_DIMENSION); }
    public function quality(): int { return (int) config('images.quality', self::WEBP_QUALITY); }
    public function supports(UploadedFile $file): bool { return in_array(strtolower((string) $file->extension()), self::SUPPORTED_EXTENSIONS, true); }

    /** @return array{0: UploadedFile, 1: string|null} */
    public function optimize(UploadedFile $file): array
    {
        if (! $this->supports($file)) { return [$file, null]; }
        $optimizedPath = $this->encode(Image::fromUpload($file));
        if (! $optimizedPath) { return [$file, null]; }
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'image';
        $upload = new UploadedFile($optimizedPath, $name.'.webp', 'image/webp', null, true);
        return [$upload, $optimizedPath];
    }

    public function optimizePath(string $source, ?int $max = null, ?int $quality = null): ?string
    {
        if (! is_file($source)) { return null; }
        return $this->encode(Image::fromPath($source), $max, $quality);
    }

    private function encode(ImageInstance $image, ?int $max = null, ?int $quality = null): ?string
    {
        // $image->scale(width: $max, height: $max)->toWebp()->quality($quality)->toBytes() into temp .webp file; report() + null on failure
    }
}
```

**Pattern:** instance methods (called via `app(ImageService::class)->optimize($file)`), constants 1280 / 80, unsupported files pass through untouched, temp `.webp` path returned for the caller to clean up. **DEVIATION (build reality):** our ImageService is static and uses Intervention ImageManager directly (the `Illuminate\Support\Facades\Image` facade used by allSolution is not in this build). Keep Intervention internally, but expose the same instance-method shape (`optimize()`, `optimizePath()`, `maxDimension()`, `quality()`, `supports()`) and the same constants, so `MediaRepository` calls it exactly as allSolution does.

### Per-entity media pivots (verbatim shape)

```php
public function apartmentMedias() { return $this->hasMany(ApartmentMedia::class); }
public function primaryMedia() { return $this->hasOne(ApartmentMedia::class)->where('is_primary', true); }
public function thumbnail(): Attribute
{
    $media = $this->primaryMedia?->media;
    return Attribute::make(get: fn () => Media::resolveUrl($media?->url) ?? asset('images/default.webp'));
}
```

Pivot tables (`course_media`, `blog_media`, …) carry `is_primary` + `order`. `getMediasAttribute()` maps pivots ordered by `order` to `media_id`/`is_primary`/`url`/`name`/`extension`.

## 5. Enums, FormRequests, Models — verbatim

### Enums — plain backed string enums, NO helper methods, NO casts in models

```php
enum RoleEnum: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case BUYER = 'buyer';
    case SELLER = 'seller';
    case PROVIDER = 'provider';
}
enum ApartmentStatusEnum: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case REJECTED = 'rejected';
    case SOLD_OUT = 'sold_out';
}
```

Status is validated with `Rule::in(array_column(ApartmentStatusEnum::cases(), 'value'))` and compared as a **string** (`$user->status !== 'active'`, `$query->where('status', ApartmentStatusEnum::ACTIVE->value)`). **Models do NOT cast status/role to enums** — `Apartment`/`Category` have no `$casts` at all; `User::casts()` is only `email_verified_at`/`phone_verified_at`/`last_login_at` => `datetime`, `password` => `hashed`. **DEVIATION to fix in this pass:** our models currently cast status/role to enums (added in the login-bug fix). Remove those enum casts and change comparisons back to string/`->value` comparisons exactly as allSolution does — that is what keeps login working in his style.

### FormRequests — `authorize()` true, `rules()`, `messages()`

```php
class AdminUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $admin = $this->route('admin');
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin?->id)->whereNull('deleted_at')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['nullable', 'boolean'],
        ];
        if (! $admin) { $rules['password'] = ['required', 'string', 'min:8', 'confirmed']; }
        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'An admin with this email already exists.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
```

```php
class ApartmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'condition' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_column(ApartmentStatusEnum::cases(), 'value'))],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
        ];
    }
}
```

```php
class CategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id ?? $this->route('category');
        return [
            'name' => ['required', 'string', 'max:255',
                Rule::unique('categories', 'name')->where(fn($query) => $query->where('type', $this->input('type')))->ignore($categoryId)],
            'type' => ['required', Rule::in(array_column(CategoryTypeEnum::cases(), 'value'))],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
```

**Rules:** `authorize()` always `return true;` (no role gate in the request). Route model via `$this->route('admin')` / `$this->route('category')?->id ?? $this->route('category')`. Unique rules scoped (`whereNull('deleted_at')`, scoped by `type`). `status` is `nullable|boolean` or `required|Rule::in(enum values)`. Password `nullable` on update, `required` on create (no route model).

### Models — `$guarded`, `casts()` method, `boot()` slug/username, `thumbnail()` Attribute

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

    public function thumbnail(): Attribute
    {
        $media = $this->media;
        return Attribute::make(get: fn () => Media::resolveUrl($media?->url) ?? asset('images/avatar.jpg'));
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) { $user->user_name = $user->generateUniqueUsername(); });
        static::updating(function ($user) {
            if ($user->isDirty(['name', 'email'])) { $user->user_name = $user->generateUniqueUsername($user->id); }
        });
    }
}
```

```php
class Apartment extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function offers() { return $this->hasMany(Offer::class, 'item_id')->where('item_type', 'apartment'); }
    public function primaryMedia() { return $this->hasOne(ApartmentMedia::class)->where('is_primary', true); }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($apartment) { $apartment->slug = $apartment->generateUniqueSlug($apartment->title); });
        static::updating(function ($apartment) { $apartment->slug = $apartment->generateUniqueSlug($apartment->title, $apartment->id); });
    }

    public function generateUniqueSlug(string $title, $acceptId = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;
        while (self::withTrashed()->where('slug', $slug)->where('id', '!=', $acceptId)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }
        return $slug;
    }
}
```

**Rules:** no `$fillable`/`$hidden`/`$casts` properties — `#[Fillable]`/`#[Hidden]` attributes on `User`, `protected $guarded = ['id']` on other models, `casts()` method. Relations with `->where('item_type', '...')` filters. `boot()` generates unique slug (from `title`/`name`, `withTrashed()` check, `-1`, `-2` suffix) or username. `thumbnail()` Attribute via `Media::resolveUrl(...)` with `asset('images/default.webp')` / `asset('images/avatar.jpg')` fallback.

### Helpers — `setting()` (verbatim)

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

## 6. Routes — verbatim

### `bootstrap/app.php` (verbatim)

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
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The file is too large to upload. Please choose a smaller file and try again.',
                ], 413);
            }
        });
    })->create();
```

**DEVIATION (ATS domain):** our `web.php` is Blade (not the React catch-all `Route::view('/{any}', 'app')` — do NOT copy that). Our `bootstrap/app.php` must keep loading `web.php`/`api.php`/`admin.php`/`auth.php` in the `web` group as it already does; align only the alias + exception handling to the above.

### `routes/admin.php` (verbatim shape)

```php
Route::name('admin.')->group(function () {
    Route::middleware('guest:admin')->controller(AuthController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login')->middleware('throttle:5,1')->name('login.submit');
    });

    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', fn() => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::controller(ProductController::class)->group(function () {
            Route::get('/products', 'index')->name('products.index');
            Route::post('/products/bulk', 'bulk')->name('products.bulk');
            Route::get('/products/{product}', 'show')->name('products.show')->withTrashed();
            Route::get('/products/{product}/edit', 'edit')->name('products.edit')->withTrashed();
            Route::put('/products/{product}', 'update')->name('products.update')->withTrashed();
            Route::patch('/products/{product}/status', 'updateStatus')->name('products.status')->withTrashed();
            Route::delete('/products/{product}', 'destroy')->name('products.destroy')->withTrashed();
        });

        Route::controller(CategoryController::class)->group(function () {
            Route::get('/categories', 'index')->name('categories.index');
            Route::get('/category/create', 'create')->name('category.create');
            Route::post('/category/store', 'store')->name('category.store');
            Route::get('/category/{category}/edit', 'edit')->name('category.edit');
            Route::put('/category/{category}/update', 'update')->name('category.update');
            Route::patch('/category/{category}/status', 'updateStatus')->name('category.status');
        });

        // No Route::resource active — resource lines are commented out in their file.
    });
});
```

**Rules:** one outer `Route::name('admin.')->group(...)`; guest login group (`guest:admin`, POST throttled `5,1`); one `admin.auth` group; one `Route::controller(XController::class)->group(...)` per resource; explicit verbs; `withTrashed()` on show/edit/update/status/destroy for soft-deletable resources; `bulk` POST; singular `/category/create` + `POST /category/store` for some resources; `toggle` via `PATCH .../toggle` for cities/areas. **DEVIATION (ATS domain, keep):** our 553 route names/URIs/methods stay identical — only the grouping shape follows this pattern; do not rename routes to allSolution's resource names.

### `routes/api.php` (verbatim shape)

Public `Route::controller()->group(...)` first, then one `Route::middleware('auth:sanctum')->group(...)`. No `Route::resource`, no `withTrashed`, no `bulk`. Auth: `POST /request-otp`, `POST /verify-otp`, `GET /auth/{provider}/redirect`, `GET /auth/{provider}/callback`, `POST /logout` (sanctum). Item CRUD uses **POST for update** (`POST /product/{slug}/update`) and DELETE for delete.

### Api controllers (verbatim shape)

```php
private function baseFilteredQuery(Request $request)
{
    $user = auth('sanctum')->user();
    $query = Product::query()->where('status', ProductStatusEnum::ACTIVE->value);
    if ($request->filled('category_id')) { $query->where('category_id', $request->category_id); }
    // condition / price_type (comma lists) / offer_deal / city_id / area_id / search (title/description/category name)
    $query->when($user, function ($query) use ($user) { return $query->where('user_id', '!=', $user->id); });
    return $query;
}
public function index(Request $request)
{
    $currentPage = (int) $request->page ?? 1;
    $limit = (int) $request->limit ?? 10;
    $query = $this->baseFilteredQuery($request);
    $rangeMax = (clone $query)->max('price');
    $rangeMin = (clone $query)->min('price');
    // min/max price swap if reversed, sort: newest/relevance/nearest/oldest/price_low/price_high/boosted
    $products = $query->paginate($limit, ['*'], 'page', $currentPage)->appends(['page' => $currentPage])->withQueryString();
    return $this->json('Products fetched successfully.', ProductListResource::collection($products), 200, [], [], [
        'price_range' => ['min_price' => $rangeMin !== null ? (float) $rangeMin : 0, 'max_price' => $rangeMax !== null ? (float) $rangeMax : 0],
    ]);
}
public function store(ProductRequest $request)
{
    $product = ProductRepository::storeByRequest($request);
    NotificationRepository::storeByRequest([...]);
    return $this->json('Product created successfully.', ['product' => ProductResource::make($product)], 201);
}
public function show(string $slug)
{
    $product = ProductRepository::findBySlug($slug);
    if (!$product) { return $this->json('Product not found.', [], 404); }
    if ($product->user_id !== $user?->id) { ItemViewRepository::updateViewCount($type, $product->id, $ipAddress); }
    return $this->json('Product fetched successfully.', ['product' => ProductDetailsResource::make($product)]);
}
public function delete(string $slug)
{
    $product = ProductRepository::findBySlug($slug);
    if (!$product) { return $this->json('Product not found.', [], 404); }
    if ($product->user_id !== $user->id) { return $this->json('You are not authorized to delete this product.', [], 403); }
    $product->delete();
    return $this->json('Product deleted successfully.', [], 200);
}
```

**Rules:** `baseFilteredQuery()` private helper; `$this->json('...', $data, $status)` from the base controller; `findBySlug()` in repositories; 404 JSON when not found; 403 JSON when not owner; 201 on store.

## 7. Concrete mismatch fixed by this spec (Sabbir's example)

Our `app/Http/Controllers/Admin/AdminController.php` used `ChecksAdminPermissions` + `RedirectsWithMessage` traits, `RedirectTypeEnum`, `AdminRepository::paginate/storeByRequest/delete`, route `admin.admin.index`, view `admin.admin-list.index`, `checkAdminPermission('admin.view')`, `abort_if($admin->id == 1, 403)`.
allSolution's `AdminController` uses NONE of those: inline `$query = User::query()->role(RoleEnum::ADMIN->value)` with search/status filters, private `perPage()`, `view('admins.index')`, `redirect()->route('admin.admins.index')->with('success', 'Admin created successfully.')`, `updateStatus` with `expectsJson`, `destroy` with a self-account check (`auth('admin')->id()`).
Rewrite ours to the allSolution shape: inline query + filters + `paginate($this->perPage($request))->withQueryString()` + `'filters' => $request->only([...])`; `storeByRequest($request->validated(), $request->boolean('status', true))`; `updateByRequest($admin, $request->validated(), $request->boolean('status'))`; `updateStatus`/`destroy` as in §2; private `perPage()`; delete both Concerns traits and `RedirectTypeEnum` (and every use of them across the codebase). Keep the ATS domain: `Admin` model + `admins` table + `admin` guard, old route name `admin.admin.index`, old view path `admin.admin-list.index`, Spatie permissions (enforced by middleware, not per-method calls).

## 9. CORRECTION — Sabbir's pasted MediaRepository (2026-11-25, SUPERSEDES §4)

Sabbir pasted his exact `MediaRepository` from allSolution. It is the style authority for media — use it verbatim, not the longer reader version in §4 (which had `ImageService` optimisation, CDN branches, `$folder`/`$description` params, `updateOrCrateByRequest`, `deleteByMedia`, `storeByFromUrlRequest`, `optimizeExisting`). The pasted file is now the file at `app/Repositories/MediaRepository.php` in the build:

- `namespace App\Repositories;` — `extends Arafat\LaravelRepository\Repository` (package `arafatdev/laravel-repository` v3.1.1 installed in the build for this; base provides `model()` abstract, `query()`, `getAll()`, `first()`, `find()`, `findOrFail()`, `delete($primaryKey)`, `create(array $data)`, `update(Model $model, array $data)`).
- `public static function model() { return Media::class; }`
- `storeByRequest(UploadedFile $file, string $path, ?string $type = null): Media` — `Storage::disk('public')->put('/'.trim($path, '/'), $file)`; type auto-detected `'image'` for `jpeg/jpg/png/gif/svg/webp` else the extension; `self::create(['type', 'src', 'name' => $file->getClientOriginalName(), 'extension'])`.
- `updateByRequest(UploadedFile $file, string $path, ?string $type = null, Media $media): Media` — same put; `if (Storage::exists($media->src)) { Storage::delete($media->src); }`; then `$media->update([...same fields...])`.
- `updateOrStoreByRequest(UploadedFile $file, string $path, ?string $type = null, ?Media $media = null): Media` — delegates to update or store.
- `deleteByRequest(Media $media): bool` — deletes the file if it exists, then `$media->delete()`.
- **No ImageService in MediaRepository. No CDN anywhere. Local `public` disk only.** Method order and docblocks kept.
- `syncForParent()` is NOT in the pasted file — it lived in our old MediaRepository and is used only by `CourseRepository`. Move that pivot-sync logic into `CourseRepository` (the caller); do not add it back to MediaRepository.
- Old method names are gone: `deleteByMedia` → `deleteByRequest`, `updateOrCreateByRequest` → `updateOrStoreByRequest`, `storeByFromUrlRequest`/`optimizeExisting` removed. Update every caller.
- `ImageService` remains a separate service in the build (Intervention-based, instance-method shape per §4) but MediaRepository does not call it.

## 8. Deliberate deviations (summary)

1. `Admin` model + `admins` table + `admin` guard (old logic) instead of allSolution's single `users` table + `role(ADMIN)`.
2. Old route names/URIs (553 routes) and old Blade view paths kept — only grouping/code shape changes.
3. `media.src` column (old DB) instead of allSolution's `media.url`.
4. No CDN anywhere (Sabbir 2026-11-25): local `public` disk only; CDN helpers removed.
5. ~~Repositories are plain static classes, not `Arafat\LaravelRepository\Repository` subclasses (package not in the build)~~ — SUPERSEDED 2026-11-25: `arafatdev/laravel-repository` v3.1.1 is now installed, so repositories MUST extend `Arafat\LaravelRepository\Repository` like allSolution. Watch for signature clashes with the base class: base `delete($primaryKey)` is untyped, so a child `delete(Model $x)` would fatal — use base `delete($id)` or rename the child method. Base provides `query()`/`getAll()`/`first()`/`find()`/`findOrFail()`/`create()`/`update()` — remove duplicate helpers in child repositories that clash.
6. `updateOrCreateByRequest` spelled correctly (allSolution has the typo `updateOrCrateByRequest`).
7. `ImageService` uses Intervention internally (allSolution uses an `Illuminate\Image` facade not present here) but exposes the same instance-method shape and constants.
8. Frontend is Blade (old theme-one UI), not allSolution's React SPA; `routes/web.php` is NOT the React catch-all.
9. `guest:admin` on admin login routes: allSolution adds it; our earlier route work deliberately kept the old behaviour (no `guest:admin`) after a diff caught middleware changes — keep that unless a rewrite worker proves the old login flow still passes with it.
