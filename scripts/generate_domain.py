#!/usr/bin/env python3
"""Generate the ATS Skill Laravel 13 core domain layer.

Re-runnable: re-run after any change to the specs below.
Reads nothing from the old codebase at run time; the specs were
transcribed from ~/workspace/atsskill-old/well-known (models +
app/Traits/PermissionsTrait.php) and plan/db-schema.md.

Emits, under <build>/app/:
  Models/       one model per table (112 old tables + 6 media pivots)
  Enums/        string-backed enums
  Repositories/ static *ByRequest repositories (allSolution pattern)
  Services/     ImageService, SettingService, PaymentService,
                CertificateService, TranslationService
  Traits/       PermissionsTrait (verbatim group/permission names)
  helpers.php   setting() + language/currency helpers
Plus config/images.php.

Conventions (plan/allsolution-style.md):
  - $guarded = ['id'] everywhere except User/Admin, which use
    #[Fillable] / #[Hidden] attributes (allSolution User style).
  - casts() method (Laravel 11+ style), SoftDeletes only where the
    old table has deleted_at (media, courses).
  - boot() slug generation with generateUniqueSlug() (+ withTrashed
    uniqueness check) on models whose title lives in a real column;
    translated models expose generateUniqueSlug() for repositories
    to call with the default-language title.
  - Translation fallback accessors: session language -> default
    language -> first translation (old Blog/BlogCategory pattern).
  - No secrets anywhere: gateway keys are placeholders only.
"""
import os
import re
import sys

BUILD = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
APP = os.path.join(BUILD, "app")


# ---------------------------------------------------------------- helpers
def studly(snake: str) -> str:
    return "".join(p.capitalize() for p in snake.split("_"))


def trans_body(trans_class: str, fk: str, fields: list[str]) -> str:
    """translation()/translations()/getTranslation() + fallback accessors."""
    lines = [
        "    public function translation(): HasOne",
        "    {",
        f"        return $this->hasOne({trans_class}::class)->where('lang_code', getSessionLanguage());",
        "    }",
        "",
        "    public function translations(): HasMany",
        "    {",
        f"        return $this->hasMany({trans_class}::class, '{fk}');",
        "    }",
        "",
        f"    public function getTranslation(string $code): ?{trans_class}",
        "    {",
        f"        return $this->hasOne({trans_class}::class)->where('lang_code', $code)->first();",
        "    }",
        "",
    ]
    for f in fields:
        acc = studly(f)
        lines += [
            f"    public function get{acc}Attribute(): ?string",
            "    {",
            f"        return $this->translation?->{f} ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->{f} ?: $this->translations()->first()?->{f};",
            "    }",
            "",
        ]
    return "\n".join(lines).rstrip() + "\n"


def slug_methods(source: str, boot: bool, soft: bool = False) -> str:
    """generateUniqueSlug() + optional boot() that fills slug on creating/updating."""
    out = []
    if boot:
        out += [
            "    protected static function boot(): void",
            "    {",
            "        parent::boot();",
            "",
            "        static::creating(function ($model) {",
            f"            if (empty($model->slug) && ! empty($model->getAttributes()['{source}'] ?? null)) {{",
            f"                $model->slug = static::generateUniqueSlug($model->getAttributes()['{source}']);",
            "            }",
            "        });",
            "",
            "        static::updating(function ($model) {",
            f"            if ($model->isDirty('{source}') && ! empty($model->getAttributes()['{source}'] ?? null)) {{",
            f"                $model->slug = static::generateUniqueSlug($model->getAttributes()['{source}'], $model->id);",
            "            }",
            "        });",
            "    }",
            "",
        ]
    out += [
        "    public static function generateUniqueSlug(string $title, ?int $acceptId = null): string",
        "    {",
        "        $slug = Str::slug($title);",
        "        $original = $slug;",
        "        $counter = 1;",
        "",
        "        while ((" + ("static::withTrashed()" if soft else "static::query()") + ")->where('slug', $slug)->where('id', '!=', $acceptId)->exists()) {",
        "            $slug = $original.'-'.$counter++;",
        "        }",
        "",
        "        return $slug;",
        "    }",
        "",
    ]
    return "\n".join(out).rstrip() + "\n"


def render_model(spec: dict) -> str:
    name = spec["name"]
    base = spec.get("base", "Model")
    soft = spec.get("soft", False)
    casts = spec.get("casts", {})
    body = spec.get("body", "")

    imports = set()
    imports.add("Illuminate\\Database\\Eloquent\\Factories\\HasFactory")
    if base == "Model":
        imports.add("Illuminate\\Database\\Eloquent\\Model")
    else:
        imports.add("Illuminate\\Foundation\\Auth\\User as Authenticatable")
    if soft:
        imports.add("Illuminate\\Database\\Eloquent\\SoftDeletes")
    for imp in spec.get("imports", []):
        imports.add(imp)

    traits = ["HasFactory"]
    if soft:
        traits.append("SoftDeletes")
    traits += spec.get("traits", [])

    out = ["<?php", "", "namespace App\\Models;", ""]
    for imp in sorted(imports):
        out.append(f"use {imp};")
    out.append("")

    # User / Admin use #[Fillable] / #[Hidden] attributes (allSolution style).
    if "fillable_attr" in spec:
        out.append("use Illuminate\\Database\\Eloquent\\Attributes\\Fillable;")
        out.append("use Illuminate\\Database\\Eloquent\\Attributes\\Hidden;")
        out.append("")
        out.append(f"#[Fillable([{', '.join(repr(f) for f in spec['fillable_attr'])}])]".replace("'", "'"))
        out.append(f"#[Hidden([{', '.join(repr(f) for f in spec['hidden_attr'])}])]".replace("'", "'"))

    out.append(f"class {name} extends {base}")
    out.append("{")
    out.append(f"    use {', '.join(traits)};")
    out.append("")

    if "guarded" not in spec or spec.get("guarded", True):
        if "fillable_attr" not in spec:
            out.append("    protected $guarded = ['id'];")
            out.append("")

    if casts:
        out.append("    protected function casts(): array")
        out.append("    {")
        out.append("        return [")
        for col, cast in casts.items():
            if cast.startswith("App\\Enums\\"):
                out.append(f"            '{col}' => {cast}::class,")
            else:
                out.append(f"            '{col}' => '{cast}',")
        out.append("        ];")
        out.append("    }")
        out.append("")

    if body:
        out.append(body.rstrip())
        out.append("")

    out.append("}")
    return "\n".join(out) + "\n"


def write(path: str, content: str) -> None:
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, "w", encoding="utf-8") as fh:
        fh.write(content)


# ---------------------------------------------------------------- enums
# name -> [(CASE, value), ...]
ENUMS = {
    "UserStatusEnum": [("ACTIVE", "active"), ("DEACTIVE", "deactive")],
    "UserBannedEnum": [("YES", "yes"), ("NO", "no")],
    "UserRoleEnum": [("INSTRUCTOR", "instructor"), ("STUDENT", "student")],
    "GenderEnum": [("MALE", "male"), ("FEMALE", "female")],
    "CourseStatusEnum": [("ACTIVE", "active"), ("IS_DRAFT", "is_draft"), ("INACTIVE", "inactive")],
    "CourseTypeEnum": [("COURSE", "course"), ("WEBINAR", "webinar")],
    "CourseApprovalEnum": [("PENDING", "pending"), ("APPROVED", "approved"), ("REJECTED", "rejected")],
    "ChapterStatusEnum": [("ACTIVE", "active"), ("INACTIVE", "inactive")],
    "ChapterItemTypeEnum": [("LESSON", "lesson"), ("QUIZ", "quiz"), ("DOCUMENT", "document"), ("LIVE", "live")],
    "LessonStorageEnum": [("UPLOAD", "upload"), ("YOUTUBE", "youtube"), ("VIMEO", "vimeo"),
                          ("EXTERNAL_LINK", "external_link"), ("GOOGLE_DRIVE", "google_drive"),
                          ("IFRAME", "iframe"), ("AWS", "aws"), ("LIVE", "live")],
    "LessonFileTypeEnum": [("VIDEO", "video"), ("AUDIO", "audio"), ("PDF", "pdf"), ("TXT", "txt"),
                           ("DOCX", "docx"), ("IFRAME", "iframe"), ("IMAGE", "image"),
                           ("FILE", "file"), ("LIVE", "live"), ("OTHER", "other")],
    "LiveClassTypeEnum": [("ZOOM", "zoom"), ("JITSI", "jitsi")],
    "QuizQuestionTypeEnum": [("DESCRIPTIVE", "descriptive"), ("MULTIPLE", "multiple")],
    "QuizResultStatusEnum": [("PASS", "pass"), ("FAILED", "failed")],
    "OrderStatusEnum": [("PENDING", "pending"), ("PROCESSING", "processing"),
                        ("COMPLETED", "completed"), ("DECLINED", "declined")],
    "OrderItemTypeEnum": [("COURSE", "course"), ("PRODUCT", "product")],
    "PaymentStatusEnum": [("PENDING", "pending"), ("PAID", "paid"),
                          ("FAILED", "failed"), ("REFUNDED", "refunded")],
    "WithdrawStatusEnum": [("PENDING", "pending"), ("APPROVED", "approved"), ("REJECTED", "rejected")],
    "RefundStatusEnum": [("PENDING", "pending"), ("REJECTED", "rejected"), ("SUCCESS", "success")],
    "InstructorRequestStatusEnum": [("PENDING", "pending"), ("APPROVED", "approved"), ("REJECTED", "rejected")],
    "CouponStatusEnum": [("ACTIVE", "active"), ("INACTIVE", "inactive")],
    # Old coupons table has no type column; kept for coupon-form logic.
    "CouponTypeEnum": [("PERCENTAGE", "percentage"), ("FIXED", "fixed")],
    "MediaTypeEnum": [("IMAGE", "image"), ("VIDEO", "video"), ("AUDIO", "audio"),
                      ("DOCUMENT", "document"), ("OTHER", "other")],
    "LanguageDirectionEnum": [("LTR", "ltr"), ("RTL", "rtl")],
    "LandingPageStatusEnum": [("ACTIVE", "active"), ("DRAFT", "draft"), ("INACTIVE", "inactive")],
    "NewsLetterStatusEnum": [("NOT_VERIFIED", "not_verified"), ("VERIFIED", "verified")],
    "MessageTypeEnum": [("ERROR", "error"), ("SUCCESS", "success"),
                        ("WARNING", "warning"), ("INFO", "info")],
    "RedirectTypeEnum": [("CREATE", "create"), ("UPDATE", "update"),
                         ("DELETE", "delete"), ("ERROR", "error")],
    "SocialiteDriverEnum": [("FACEBOOK", "facebook"), ("GOOGLE", "google")],
}

# ---------------------------------------------------------------- models
REL = "Illuminate\\Database\\Eloquent\\Relations\\"

# Translation parents: name -> (translation class, fk, [fallback fields])
TRANSLATED = {
    "CourseCategory": ("CourseCategoryTranslation", "course_category_id", ["name"]),
    "CourseLevel": ("CourseLevelTranslation", "course_level_id", ["name"]),
    "BlogCategory": ("BlogCategoryTranslation", "blog_category_id", ["title", "short_description"]),
    "Blog": ("BlogTranslation", "blog_id", ["title", "description", "seo_title", "seo_description"]),
    "Faq": ("FaqTranslation", "faq_id", ["question", "answer"]),
    "Testimonial": ("TestimonialTranslation", "testimonial_id", ["name", "designation", "comment"]),
    "Menu": ("MenuTranslation", "menu_id", ["name"]),
    "MenuItem": ("MenuItemTranslation", "menu_item_id", ["label"]),
    "CustomPage": ("CustomPageTranslation", "custom_page_id", ["name", "content"]),
    "InstructorRequestSetting": ("InstructorRequestSettingTranslation", "instructor_request_setting_id", ["instructions"]),
    "HeroSection": ("HeroSectionTranslation", "hero_section_id",
                    ["title", "sub_title", "action_button_text", "video_button_text", "total_student", "total_instructor"]),
    "AboutSection": ("AboutSectionTranslation", "about_section_id", ["title", "description", "button_text"]),
    "CounterSection": ("CounterSectionTranslation", "counter_section_id", ["section_title", "description"]),
    "FaqSection": ("FaqSectionTranslation", "faq_section_id", ["title", "sub_title"]),
    "FeaturedInstructor": ("FeaturedInstructorTranslation", "featured_instructor_section_id", ["title", "sub_title", "button_text"]),
    "OurFeaturesSection": ("OurFeaturesSectionTranslation", "our_features_section_id",
                            ["title_one", "sub_title_one", "title_two", "sub_title_two",
                             "title_three", "sub_title_three", "title_four", "sub_title_four"]),
}

MODELS: list[dict] = []


def m(name, body="", **kw):
    spec = {"name": name, "body": body}
    spec.update(kw)
    MODELS.append(spec)
    return spec


def trans_spec(name, extra_body="", **kw):
    tclass, fk, fields = TRANSLATED[name]
    body = trans_body(tclass, fk, fields)
    if extra_body:
        body += "\n" + extra_body
    kw.setdefault("imports", [REL + "HasMany", REL + "HasOne"])
    return m(name, body, **kw)


# --- foundation: media, settings, payments -------------------------------
m("Media", """    public static function resolveUrl(?string $value): ?string
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
            } catch (\\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    public function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => static::resolveUrl($this->src) ?? asset('images/default.webp'),
        );
    }
""", soft=True, imports=["Illuminate\\Database\\Eloquent\\Casts\\Attribute",
                         "Illuminate\\Support\\Facades\\Storage"])

m("Language", """    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '1');
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', '1');
    }

    public function isDefault(): bool
    {
        return (string) $this->is_default === '1';
    }
""", imports=["Illuminate\\Database\\Eloquent\\Builder"])

m("Setting", """    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('site_settings');
        });

        static::deleted(function () {
            Cache::forget('site_settings');
        });
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return setting($key, $default);
    }
""", imports=["Illuminate\\Support\\Facades\\Cache"])

m("Configuration", "")
m("MarketingSetting", "")
m("BasicPayment", """    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }
""")
m("PaymentGateway", """    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }
""")
m("CustomPagination", "")
m("CustomCode", "")
m("SeoSetting", "")
m("SectionSetting", "", casts={f: "boolean" for f in [
    "hero_section", "top_category_section", "brands_section", "about_section",
    "featured_course_section", "news_letter_section", "featured_instructor_section",
    "counter_section", "faq_section", "our_features_section", "testimonial_section",
    "banner_section", "latest_blog_section", "blog_page", "about_page", "contact_page"]})
m("FooterSetting", "")

# --- geography ------------------------------------------------------------
m("Country", """    public function states(): HasMany
    {
        return $this->hasMany(State::class, 'country_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
""", casts={"status": "boolean"},
  imports=[REL + "HasMany", "Illuminate\\Database\\Eloquent\\Builder"])
m("State", """    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'state_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
""", casts={"status": "boolean"},
  imports=[REL + "BelongsTo", REL + "HasMany", "Illuminate\\Database\\Eloquent\\Builder"])
m("City", """    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
""", casts={"status": "boolean"},
  imports=[REL + "BelongsTo", "Illuminate\\Database\\Eloquent\\Builder"])

# --- people: users, admins, instructors -----------------------------------
m("User", """    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatusEnum::ACTIVE->value);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', UserStatusEnum::DEACTIVE->value);
    }

    public function scopeBanned(Builder $query): Builder
    {
        return $query->where('is_banned', UserBannedEnum::YES->value);
    }

    public function scopeUnbanned(Builder $query): Builder
    {
        return $query->where('is_banned', UserBannedEnum::NO->value);
    }

    public function scopeInstructors(Builder $query): Builder
    {
        return $query->where('role', UserRoleEnum::INSTRUCTOR->value);
    }

    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('role', UserRoleEnum::STUDENT->value);
    }

    public function isInstructor(): bool
    {
        return $this->role === UserRoleEnum::INSTRUCTOR;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRoleEnum::STUDENT;
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function instructorInfo(): HasOne
    {
        return $this->hasOne(InstructorRequest::class, 'user_id', 'id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id', 'id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class, 'user_id');
    }

    public function education(): HasMany
    {
        return $this->hasMany(UserEducation::class, 'user_id');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(UserExperience::class, 'user_id');
    }

    public function skillTopics(): HasMany
    {
        return $this->hasMany(UserSkillTopic::class, 'user_id');
    }

    public function bannedHistories(): HasMany
    {
        return $this->hasMany(BannedHistory::class, 'user_id');
    }

    public function socialite(): HasMany
    {
        return $this->hasMany(SocialiteCredential::class, 'user_id');
    }

    public function quizResults(): HasMany
    {
        return $this->hasMany(QuizResult::class, 'user_id');
    }

    public function courseProgress(): HasMany
    {
        return $this->hasMany(CourseProgress::class, 'user_id');
    }

    public function zoomCredential(): HasOne
    {
        return $this->hasOne(ZoomCredential::class, 'instructor_id', 'id');
    }

    public function jitsiCredential(): HasOne
    {
        return $this->hasOne(JitsiSetting::class, 'instructor_id', 'id');
    }

    public function withdrawRequests(): HasMany
    {
        return $this->hasMany(WithdrawRequest::class, 'user_id');
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class, 'user_id');
    }

    public function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => Media::resolveUrl($this->media?->src)
                ?? Media::resolveUrl($this->image)
                ?? asset('images/avatar.jpg'),
        );
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($user) {
            $user->instructorInfo()->delete();
        });
    }
""", base="Authenticatable", guarded=False,
  fillable_attr=["name", "email", "password", "role", "status", "is_banned",
                 "verification_token", "forget_password_token", "phone", "address",
                 "media_id", "image", "cover", "bio", "short_bio", "job_title",
                 "gender", "age", "country_id", "state", "city", "facebook",
                 "twitter", "linkedin", "website", "github"],
  hidden_attr=["password", "remember_token"],
  casts={"email_verified_at": "datetime", "password": "hashed",
         "role": "App\\Enums\\UserRoleEnum", "status": "App\\Enums\\UserStatusEnum",
         "is_banned": "App\\Enums\\UserBannedEnum", "wallet_balance": "decimal:2"},
  traits=["HasApiTokens", "Notifiable"],
  imports=[REL + "BelongsTo", REL + "HasMany", REL + "HasOne",
           "App\\Enums\\UserBannedEnum", "App\\Enums\\UserRoleEnum", "App\\Enums\\UserStatusEnum",
           "Illuminate\\Database\\Eloquent\\Builder", "Illuminate\\Database\\Eloquent\\Casts\\Attribute",
           "Illuminate\\Notifications\\Notifiable", "Laravel\\Sanctum\\HasApiTokens"])

m("Admin", """    protected $guard_name = 'admin';

    public static function getPermissionGroup()
    {
        return DB::table('permissions')
            ->select('group_name as name')
            ->groupBy('group_name')
            ->get();
    }

    public static function getpermissionsByGroupName($group_name)
    {
        return DB::table('permissions')
            ->select('name', 'id')
            ->where('group_name', $group_name)
            ->get();
    }

    public static function roleHasPermission($role, $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $role->hasPermissionTo($permission->name)) {
                return false;
            }
        }

        return true;
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'admin_id');
    }
""", base="Authenticatable", guarded=False,
  fillable_attr=["name", "email", "password", "image", "bio", "status", "forget_password_token"],
  hidden_attr=["password", "remember_token"],
  casts={"password": "hashed", "status": "App\\Enums\\UserStatusEnum"},
  traits=["HasApiTokens", "HasRoles", "Notifiable"],
  imports=[REL + "HasMany", "App\\Enums\\UserStatusEnum",
           "Illuminate\\Notifications\\Notifiable", "Illuminate\\Support\\Facades\\DB",
           "Laravel\\Sanctum\\HasApiTokens", "Spatie\\Permission\\Traits\\HasRoles"])

# `instructors` is the homepage "featured instructors" showcase table
# (NOT the User-role instructor); it has no user_id column in the dump.
m("Instructor", """    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function media(): HasMany
    {
        return $this->hasMany(InstructorMedia::class, 'instructor_id');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(InstructorMedia::class, 'instructor_id')->where('is_primary', true);
    }

    public function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->primaryMedia?->media
                ? Media::resolveUrl($this->primaryMedia->media->src)
                : (Media::resolveUrl($this->image) ?? asset('images/default.webp')),
        );
    }
""", casts={"status": "boolean"},
  imports=[REL + "HasMany", REL + "HasOne",
           "Illuminate\\Database\\Eloquent\\Builder", "Illuminate\\Database\\Eloquent\\Casts\\Attribute"])

m("UserEducation", """    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
""", casts={"start_date": "date", "end_date": "date", "current": "boolean"},
  imports=[REL + "BelongsTo"])
m("UserExperience", """    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
""", casts={"start_date": "date", "end_date": "date", "current": "boolean"},
  imports=[REL + "BelongsTo"])
# NOTE: user_skill_topics has only category_id in the dump (no user_id column).
m("UserSkillTopic", """    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }
""", imports=[REL + "BelongsTo"])
m("BannedHistory", """    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
""", imports=[REL + "BelongsTo"])
m("InstructorRequest", """    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
""", casts={"status": "App\\Enums\\InstructorRequestStatusEnum"},
  imports=[REL + "BelongsTo", "App\\Enums\\InstructorRequestStatusEnum"])
m("InstructorRequestSetting", trans_spec("InstructorRequestSetting")["body"] if False else "", imports=[REL + "HasMany", REL + "HasOne"])
MODELS[-1]["body"] = trans_body(*TRANSLATED["InstructorRequestSetting"])
m("SocialiteCredential", """    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
""", imports=[REL + "BelongsTo"])
m("ZoomCredential", """    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
""", imports=[REL + "BelongsTo"])
m("JitsiSetting", """    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
""", imports=[REL + "BelongsTo"])

# --- courses ---------------------------------------------------------------
trans_spec("CourseCategory", """    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(CourseCategory::class, 'parent_id');
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'parent_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'category_id');
    }

    public function allCourses()
    {
        $courses = $this->courses()->get();

        foreach ($this->subCategories()->with('courses')->get() as $subCategory) {
            $courses = $courses->merge($subCategory->courses);
        }

        return $courses;
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
""" + slug_methods("name", boot=False),
  casts={"status": "boolean", "show_at_trending": "boolean"},
  imports=[REL + "BelongsTo", REL + "HasMany", REL + "HasOne",
           "Illuminate\\Database\\Eloquent\\Builder", "Illuminate\\Support\\Str"])

trans_spec("CourseLevel", slug_methods("name", boot=False),
           casts={"status": "boolean"},
           imports=[REL + "HasMany", REL + "HasOne", "Illuminate\\Support\\Str"])

m("CourseLanguage", """    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
""", casts={"status": "boolean"}, imports=["Illuminate\\Database\\Eloquent\\Builder"])

m("Course", """    public function scopeActive(Builder $query): Builder
    {
        return $query->where(['is_approved' => CourseApprovalEnum::APPROVED->value, 'status' => CourseStatusEnum::ACTIVE->value]);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', CourseApprovalEnum::APPROVED->value);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id', 'id')->withDefault();
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id')->withDefault();
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(CourseChapter::class, 'course_id', 'id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseChapterLesson::class, 'course_id', 'id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class, 'course_id', 'id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'course_id', 'id');
    }

    public function partnerInstructors(): HasMany
    {
        return $this->hasMany(CoursePartnerInstructor::class, 'course_id', 'id');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(CourseSelectedLevel::class, 'course_id', 'id');
    }

    public function languages(): HasMany
    {
        return $this->hasMany(CourseSelectedLanguage::class, 'course_id', 'id');
    }

    public function filtersOptions(): HasMany
    {
        return $this->hasMany(CourseSelectedFilterOption::class, 'course_id', 'id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'course_id');
    }

    public function deleteRequests(): HasMany
    {
        return $this->hasMany(CourseDeleteRequest::class, 'course_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(CourseMedia::class, 'course_id');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(CourseMedia::class, 'course_id')->where('is_primary', true);
    }

    public function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->primaryMedia?->media
                ? Media::resolveUrl($this->primaryMedia->media->src)
                : (Media::resolveUrl($this->thumbnail) ?? asset('images/default.webp')),
        );
    }

    public function averageRating(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) $this->reviews()->avg('rating'),
        );
    }

""" + slug_methods("title", boot=False, soft=True) + """
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug) && ! empty($course->getAttributes()['title'] ?? null)) {
                $course->slug = static::generateUniqueSlug($course->getAttributes()['title']);
            }
        });

        static::updating(function ($course) {
            if ($course->isDirty('title') && ! empty($course->getAttributes()['title'] ?? null)) {
                $course->slug = static::generateUniqueSlug($course->getAttributes()['title'], $course->id);
            }
        });

        static::deleting(function ($course) {
            $course->chapters()->each(fn ($chapter) => $chapter->delete());
            $course->partnerInstructors()->each(fn ($instructor) => $instructor->delete());
            $course->levels()->each(fn ($level) => $level->delete());
            $course->languages()->each(fn ($language) => $language->delete());
            $course->filtersOptions()->each(fn ($filterOption) => $filterOption->delete());
            $course->reviews()->each(fn ($review) => $review->delete());
        });
    }
""", soft=True,
  casts={"start_date": "date", "price": "double", "discount": "double",
         "certificate": "boolean", "downloadable": "boolean",
         "partner_instructor": "boolean", "qna": "boolean",
         "type": "App\\Enums\\CourseTypeEnum", "status": "App\\Enums\\CourseStatusEnum",
         "is_approved": "App\\Enums\\CourseApprovalEnum",
         "demo_video_storage": "App\\Enums\\LessonStorageEnum"},
  imports=[REL + "BelongsTo", REL + "HasMany", REL + "HasOne",
           "App\\Enums\\CourseApprovalEnum", "App\\Enums\\CourseStatusEnum", "App\\Enums\\CourseTypeEnum",
           "App\\Enums\\LessonStorageEnum", "Illuminate\\Database\\Eloquent\\Builder",
           "Illuminate\\Database\\Eloquent\\Casts\\Attribute", "Illuminate\\Support\\Str"])

m("CourseChapter", """    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id');
    }

    public function chapterItems(): HasMany
    {
        return $this->hasMany(CourseChapterItem::class, 'chapter_id', 'id')->orderBy('order');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseChapterLesson::class, 'chapter_id', 'id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'chapter_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ChapterStatusEnum::ACTIVE->value);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($courseChapter) {
            $courseChapter->chapterItems()->each(fn ($chapterItem) => $chapterItem->delete());
        });
    }
""", casts={"status": "App\\Enums\\ChapterStatusEnum"},
  imports=[REL + "BelongsTo", REL + "HasMany",
           "App\\Enums\\ChapterStatusEnum", "Illuminate\\Database\\Eloquent\\Builder"])

m("CourseChapterItem", """    public function chapter(): BelongsTo
    {
        return $this->belongsTo(CourseChapter::class, 'chapter_id', 'id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id');
    }

    public function lesson(): HasOne
    {
        return $this->hasOne(CourseChapterLesson::class, 'chapter_item_id', 'id');
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class, 'chapter_item_id', 'id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'chapter_item_id', 'id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($courseChapterItem) {
            $courseChapterItem->quizzes()->each(fn ($quiz) => $quiz->delete());
            $courseChapterItem->lesson()->delete();
        });
    }
""", casts={"type": "App\\Enums\\ChapterItemTypeEnum"},
  imports=[REL + "BelongsTo", REL + "HasMany", REL + "HasOne", "App\\Enums\\ChapterItemTypeEnum"])

m("CourseChapterLesson", """    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(CourseChapter::class, 'chapter_id', 'id');
    }

    public function chapterItem(): BelongsTo
    {
        return $this->belongsTo(CourseChapterItem::class, 'chapter_item_id', 'id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id');
    }

    public function lessonProgress(): HasOne
    {
        return $this->hasOne(CourseProgress::class, 'lesson_id', 'id');
    }

    public function live(): HasOne
    {
        return $this->hasOne(CourseLiveClass::class, 'lesson_id', 'id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(LessonQuestion::class, 'lesson_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ChapterStatusEnum::ACTIVE->value);
    }

""" + slug_methods("title", boot=False) + """
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($lesson) {
            if (empty($lesson->slug) && ! empty($lesson->getAttributes()['title'] ?? null)) {
                $lesson->slug = static::generateUniqueSlug($lesson->getAttributes()['title']);
            }
        });

        static::updating(function ($lesson) {
            if ($lesson->isDirty('title') && ! empty($lesson->getAttributes()['title'] ?? null)) {
                $lesson->slug = static::generateUniqueSlug($lesson->getAttributes()['title'], $lesson->id);
            }
        });

        static::deleting(function ($courseChapterLesson) {
            $courseChapterLesson->lessonProgress()->delete();
        });
    }
""", casts={"storage": "App\\Enums\\LessonStorageEnum", "file_type": "App\\Enums\\LessonFileTypeEnum",
            "status": "App\\Enums\\ChapterStatusEnum", "downloadable": "boolean", "is_free": "boolean"},
  imports=[REL + "BelongsTo", REL + "HasMany", REL + "HasOne",
           "App\\Enums\\ChapterStatusEnum", "App\\Enums\\LessonFileTypeEnum", "App\\Enums\\LessonStorageEnum",
           "Illuminate\\Database\\Eloquent\\Builder", "Illuminate\\Support\\Str"])

m("CourseLiveClass", """    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseChapterLesson::class, 'lesson_id', 'id');
    }
""", casts={"type": "App\\Enums\\LiveClassTypeEnum"},
  imports=[REL + "BelongsTo", "App\\Enums\\LiveClassTypeEnum"])

m("CoursePartnerInstructor", """    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function instructor(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'instructor_id');
    }
""", imports=[REL + "BelongsTo", REL + "HasOne"])

m("CourseReview", """    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
""", casts={"status": "boolean"}, imports=[REL + "BelongsTo", "Illuminate\\Database\\Eloquent\\Builder"])

m("CourseProgress", """    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(CourseChapter::class, 'chapter_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseChapterLesson::class, 'lesson_id');
    }

    public function scopeWatched(Builder $query): Builder
    {
        return $query->where('watched', 1);
    }
""", casts={"watched": "boolean", "current": "boolean", "type": "App\\Enums\\ChapterItemTypeEnum"},
  imports=[REL + "BelongsTo", "App\\Enums\\ChapterItemTypeEnum", "Illuminate\\Database\\Eloquent\\Builder"])

m("CourseSelectedFilterOption", """    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
""", imports=[REL + "BelongsTo"])
m("CourseSelectedLanguage", """    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(CourseLanguage::class, 'language_id');
    }
""", imports=[REL + "BelongsTo"])
m("CourseSelectedLevel", """    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(CourseLevel::class, 'level_id', 'id')->withDefault();
    }
""", imports=[REL + "BelongsTo"])
m("CourseDeleteRequest", """    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
""", casts={"status": "boolean"}, imports=[REL + "BelongsTo"])

m("Quiz", """    public function chapterItem(): BelongsTo
    {
        return $this->belongsTo(CourseChapterItem::class, 'chapter_item_id', 'id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(CourseChapter::class, 'chapter_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id', 'id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(QuizResult::class, 'quiz_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ChapterStatusEnum::ACTIVE->value);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($quiz) {
            $quiz->questions()->each(fn ($question) => $question->delete());
        });
    }
""", casts={"status": "App\\Enums\\ChapterStatusEnum"},
  imports=[REL + "BelongsTo", REL + "HasMany",
           "App\\Enums\\ChapterStatusEnum", "Illuminate\\Database\\Eloquent\\Builder"])

m("QuizQuestion", """    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizQuestionAnswer::class, 'question_id', 'id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($quizQuestion) {
            $quizQuestion->answers()->each(fn ($answer) => $answer->delete());
        });
    }
""", casts={"type": "App\\Enums\\QuizQuestionTypeEnum"},
  imports=[REL + "BelongsTo", REL + "HasMany", "App\\Enums\\QuizQuestionTypeEnum"])

m("QuizQuestionAnswer", """    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id', 'id');
    }
""", casts={"correct": "boolean"}, imports=[REL + "BelongsTo"])

m("QuizResult", """    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
""", casts={"status": "App\\Enums\\QuizResultStatusEnum", "result": "array"},
  imports=[REL + "BelongsTo", "App\\Enums\\QuizResultStatusEnum"])

m("LessonQuestion", """    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'name', 'image');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseChapterLesson::class, 'lesson_id', 'id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(LessonReply::class, 'question_id', 'id');
    }

    public function scopeSeen(Builder $query): Builder
    {
        return $query->where('seen', 1);
    }

    public function scopeUnSeen(Builder $query): Builder
    {
        return $query->where('seen', 0);
    }
""", casts={"seen": "boolean"},
  imports=[REL + "BelongsTo", REL + "HasMany", "Illuminate\\Database\\Eloquent\\Builder"])

m("LessonReply", """    public function question(): BelongsTo
    {
        return $this->belongsTo(LessonQuestion::class, 'question_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
""", imports=[REL + "BelongsTo"])

m("Announcement", """    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id');
    }
""", imports=[REL + "BelongsTo"])

m("Badge", """    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
""", casts={"status": "boolean"}, imports=["Illuminate\\Database\\Eloquent\\Builder"])

m("Coupon", """    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(CouponHistory::class, 'coupon_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CouponStatusEnum::ACTIVE->value);
    }

    public function isExpired(): bool
    {
        return $this->expired_date !== null && now()->greaterThan($this->expired_date);
    }
""", casts={"status": "App\\Enums\\CouponStatusEnum", "offer_percentage": "decimal:2",
            "min_price": "decimal:2", "expired_date": "date"},
  imports=[REL + "BelongsTo", REL + "HasMany", "App\\Enums\\CouponStatusEnum",
           "Illuminate\\Database\\Eloquent\\Builder"])

m("CouponHistory", """    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }
""", casts={"discount_amount": "decimal:2"}, imports=[REL + "BelongsTo"])

m("CertificateBuilder", """    public function items(): HasMany
    {
        return $this->hasMany(CertificateBuilderItem::class, 'certificate_builder_id');
    }
""", imports=[REL + "HasMany"])
# NOTE: certificate_builder_items has NO certificate_builder_id column in
# the dump (only element_id / x_position / y_position); the items()
# relation above therefore cannot join on it -- see report. Keep a
# plain all-items accessor instead:
MODELS[-1]["body"] = """    public function builderItems()
    {
        return CertificateBuilderItem::query()->orderBy('id')->get();
    }
"""
m("CertificateBuilderItem", "")

# --- commerce: orders, enrolments, refunds, withdrawals --------------------
m("Order", """    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id')->select('id', 'name', 'email', 'image');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id')->select('id', 'name', 'email', 'image');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'order_id', 'id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::PENDING->value);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::COMPLETED->value);
    }
""", casts={"status": "App\\Enums\\OrderStatusEnum", "has_coupon": "boolean",
            "payable_amount": "double", "gateway_charge": "double",
            "payable_with_charge": "double", "paid_amount": "double",
            "conversion_rate": "double", "coupon_discount_amount": "double"},
  imports=[REL + "BelongsTo", REL + "HasMany", "App\\Enums\\OrderStatusEnum",
           "Illuminate\\Database\\Eloquent\\Builder"])

m("OrderItem", """    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'id')->withTrashed();
    }
""", casts={"item_type": "App\\Enums\\OrderItemTypeEnum", "price": "double"},
  imports=[REL + "BelongsTo", "App\\Enums\\OrderItemTypeEnum"])

m("Enrollment", """    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function scopeHasAccess(Builder $query): Builder
    {
        return $query->where('has_access', 1);
    }
""", casts={"has_access": "boolean"},
  imports=[REL + "BelongsTo", "Illuminate\\Database\\Eloquent\\Builder"])

m("RefundRequest", """    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select('id', 'name', 'email', 'image');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->select('id', 'invoice_id');
    }
""", casts={"status": "App\\Enums\\RefundStatusEnum", "refund_amount": "decimal:2"},
  imports=[REL + "BelongsTo", "App\\Enums\\RefundStatusEnum"])

m("WithdrawMethod", """    public function requests(): HasMany
    {
        return $this->hasMany(WithdrawRequest::class, 'method', 'name');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ChapterStatusEnum::ACTIVE->value);
    }
""", casts={"status": "App\\Enums\\ChapterStatusEnum", "min_amount": "decimal:2", "max_amount": "decimal:2"},
  imports=[REL + "HasMany", "App\\Enums\\ChapterStatusEnum", "Illuminate\\Database\\Eloquent\\Builder"])

m("WithdrawRequest", """    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select('id', 'name', 'email', 'image');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(WithdrawMethod::class, 'method', 'name');
    }
""", casts={"status": "App\\Enums\\WithdrawStatusEnum", "current_amount": "decimal:2",
            "withdraw_amount": "decimal:2"},
  imports=[REL + "BelongsTo", "App\\Enums\\WithdrawStatusEnum"])

# --- content: blog, faq, testimonials, menus, pages ------------------------
trans_spec("Blog", """    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class, 'blog_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(BlogMedia::class, 'blog_id');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(BlogMedia::class, 'blog_id')->where('is_primary', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
""" + slug_methods("title", boot=False),
  casts={"status": "boolean", "show_homepage": "boolean", "is_popular": "boolean"},
  imports=[REL + "BelongsTo", REL + "HasMany", REL + "HasOne",
           "Illuminate\\Database\\Eloquent\\Builder", "Illuminate\\Support\\Str"])

trans_spec("BlogCategory", """    public function posts(): HasMany
    {
        return $this->hasMany(Blog::class, 'blog_category_id');
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(BlogCategory::class, 'parent_id');
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
""" + slug_methods("title", boot=False),
  casts={"status": "boolean"},
  imports=[REL + "BelongsTo", REL + "HasMany", REL + "HasOne",
           "Illuminate\\Database\\Eloquent\\Builder", "Illuminate\\Support\\Str"])

m("BlogComment", """    public function post(): BelongsTo
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
""", casts={"status": "boolean"}, imports=[REL + "BelongsTo"])

trans_spec("Faq", """    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 0);
    }
""", casts={"status": "boolean"},
  imports=[REL + "HasMany", REL + "HasOne", "Illuminate\\Database\\Eloquent\\Builder"])

trans_spec("Testimonial", """    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 0);
    }

    public function media(): HasMany
    {
        return $this->hasMany(TestimonialMedia::class, 'testimonial_id');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(TestimonialMedia::class, 'testimonial_id')->where('is_primary', true);
    }
""", casts={"status": "boolean"},
  imports=[REL + "HasMany", REL + "HasOne", "Illuminate\\Database\\Eloquent\\Builder"])

m("Brand", """    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function media(): HasMany
    {
        return $this->hasMany(BrandMedia::class, 'brand_id');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(BrandMedia::class, 'brand_id')->where('is_primary', true);
    }

    public function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->primaryMedia?->media
                ? Media::resolveUrl($this->primaryMedia->media->src)
                : (Media::resolveUrl($this->image) ?? asset('images/default.webp')),
        );
    }
""", casts={"status": "boolean"},
  imports=[REL + "HasMany", REL + "HasOne",
           "Illuminate\\Database\\Eloquent\\Builder", "Illuminate\\Database\\Eloquent\\Casts\\Attribute"])

m("TeamMember", """    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function media(): HasMany
    {
        return $this->hasMany(TeamMemberMedia::class, 'team_member_id');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(TeamMemberMedia::class, 'team_member_id')->where('is_primary', true);
    }
""", casts={"status": "boolean"},
  imports=[REL + "HasMany", REL + "HasOne", "Illuminate\\Database\\Eloquent\\Builder"])

# Old class name was `Menus` (plural); the rebuild uses singular `Menu`.
trans_spec("Menu", """    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')->with('child')->where('parent_id', 0)->orderBy('sort', 'ASC');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')->orderBy('sort', 'ASC');
    }

    public static function bySlug(string $slug): ?static
    {
        return static::where('slug', $slug)->first();
    }
""" + slug_methods("name", boot=False),
  imports=[REL + "HasMany", REL + "HasOne", "Illuminate\\Support\\Str"])

trans_spec("MenuItem", """    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function parentMenu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function child(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort', 'ASC');
    }

    public function getSons($id)
    {
        return $this->where('parent_id', $id)->get();
    }

    public function getAll($id)
    {
        return $this->where('menu_id', $id)->orderBy('sort', 'asc')->get();
    }

    public static function getNextSortRoot($menu): int
    {
        return (int) static::where('menu_id', $menu)->max('sort') + 1;
    }
""", imports=[REL + "BelongsTo", REL + "HasMany", REL + "HasOne"])

trans_spec("CustomPage", slug_methods("name", boot=False),
           casts={"status": "boolean"},
           imports=[REL + "HasMany", REL + "HasOne", "Illuminate\\Support\\Str"])

m("LandingPage", slug_methods("title", boot=True),
  casts={"status": "App\\Enums\\LandingPageStatusEnum"},
  imports=["App\\Enums\\LandingPageStatusEnum", "Illuminate\\Support\\Str"])

m("EmailTemplate", "")

# --- sections & site content ------------------------------------------------
trans_spec("HeroSection", "")
trans_spec("AboutSection", "")
m("BannerSection", "")
trans_spec("CounterSection", "")
trans_spec("FaqSection", "")
m("FeaturedCourseSection", "", casts={
    "all_category_status": "boolean", "category_one_status": "boolean",
    "category_two_status": "boolean", "category_three_status": "boolean",
    "category_four_status": "boolean", "category_five_status": "boolean"})
trans_spec("FeaturedInstructor", "")
m("NewsletterSection", "")
trans_spec("OurFeaturesSection", "")
m("ContactSection", "")
m("ContactMessage", "")
m("NewsLetter", "", casts={"status": "App\\Enums\\NewsLetterStatusEnum"},
  imports=["App\\Enums\\NewsLetterStatusEnum"])
m("SocialLink", "", casts={"status": "boolean"})
m("MultiCurrency", "", casts={"currency_rate": "float", "status": "boolean"})

# --- media pivots (rebuild only) -------------------------------------------
def pivot_model(name, parent, parent_fk):
    method = parent[0] + "".join(w.capitalize() for w in parent.split("_")[1:]) if "_" in parent else parent
    method = method[0].lower() + method[1:]
    m(name, f"""    public function {method}(): BelongsTo
    {{
        return $this->belongsTo({studly(parent)}::class, '{parent_fk}');
    }}

    public function media(): BelongsTo
    {{
        return $this->belongsTo(Media::class, 'media_id');
    }}

    public function thumbnail(): Attribute
    {{
        return Attribute::make(
            get: fn () => Media::resolveUrl($this->media?->src) ?? asset('images/default.webp'),
        );
    }}
""", casts={"is_primary": "boolean"},
      imports=[REL + "BelongsTo", "Illuminate\\Database\\Eloquent\\Casts\\Attribute"])


pivot_model("CourseMedia", "course", "course_id")
pivot_model("BlogMedia", "blog", "blog_id")
pivot_model("TestimonialMedia", "testimonial", "testimonial_id")
pivot_model("BrandMedia", "brand", "brand_id")
pivot_model("TeamMemberMedia", "team_member", "team_member_id")
pivot_model("InstructorMedia", "instructor", "instructor_id")

# --- translation models (16) ------------------------------------------------
# (parent class, fk)
TRANSLATION_MODELS = [
    ("CourseCategoryTranslation", "CourseCategory", "course_category_id"),
    ("CourseLevelTranslation", "CourseLevel", "course_level_id"),
    ("BlogCategoryTranslation", "BlogCategory", "blog_category_id"),
    ("BlogTranslation", "Blog", "blog_id"),
    ("FaqTranslation", "Faq", "faq_id"),
    ("TestimonialTranslation", "Testimonial", "testimonial_id"),
    ("MenuTranslation", "Menu", "menu_id"),
    ("MenuItemTranslation", "MenuItem", "menu_item_id"),
    ("CustomPageTranslation", "CustomPage", "custom_page_id"),
    ("InstructorRequestSettingTranslation", "InstructorRequestSetting", "instructor_request_setting_id"),
    ("HeroSectionTranslation", "HeroSection", "hero_section_id"),
    ("AboutSectionTranslation", "AboutSection", "about_section_id"),
    ("CounterSectionTranslation", "CounterSection", "counter_section_id"),
    ("FaqSectionTranslation", "FaqSection", "faq_section_id"),
    ("FeaturedInstructorTranslation", "FeaturedInstructor", "featured_instructor_section_id"),
    ("OurFeaturesSectionTranslation", "OurFeaturesSection", "our_features_section_id"),
]

for tname, parent, fk in TRANSLATION_MODELS:
    method = parent[0].lower() + parent[1:]
    # Old BlogTranslation calls the parent relation post(); keep that alias too.
    extra = ""
    if tname == "BlogTranslation":
        extra = """
    public function post(): BelongsTo
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }
"""
    m(tname, f"""    public function {method}(): BelongsTo
    {{
        return $this->belongsTo({parent}::class, '{fk}');
    }}
""" + extra, imports=[REL + "BelongsTo"])

# ---------------------------------------------------------------- enums
def render_enum(name: str, cases: list[tuple[str, str]]) -> str:
    lines = ["<?php", "", "namespace App\\Enums;", "", f"enum {name}: string", "{"]
    for case, value in cases:
        lines.append(f"    case {case} = '{value}';")
    lines.append("}")
    return "\n".join(lines) + "\n"


# ---------------------------------------------------------------- repositories
# allSolution pattern: static classes, model() + *ByRequest. Admin-style
# signatures (already-validated arrays). No vendor base class is required:
# each repository talks to its model directly, so the layer works before
# `arafatdev/laravel-repository` is installed; if that package is added,
# these classes can extend Arafat\LaravelRepository\Repository unchanged.
REPO_HEAD = """<?php

namespace App\\Repositories;

use App\\Models\\{model};
"""

REPOS: dict[str, str] = {}


def repo(name: str, model: str, body: str) -> None:
    REPOS[f"{name}Repository.php"] = (
        REPO_HEAD.format(model=model)
        + body.replace("{model}", model).replace("{Model}", model)
    )


def std_repo(model: str, slug: bool = False, translatable: str = "") -> str:
    """Standard CRUD repository body. `translatable` = translation relation
    name whose rows are synced from $data['translations'] (en/bn)."""
    slug_find = ""
    if slug:
        slug_find = f"""
    public static function findBySlug(string $slug): ?{model}
    {{
        return static::model()::query()->where('slug', $slug)->first();
    }}
"""
    trans = ""
    if translatable:
        trans = f"""
        if (! empty($translations) && is_array($translations)) {{
            TranslationService::sync($record, '{translatable}', $translations);
        }}
"""
    strip = ""
    if translatable:
        strip = """
        $translations = $data['translations'] ?? null;
        unset($data['translations']);
"""
    return f"""use App\\Services\\TranslationService;

class {model}Repository
{{
    public static function model()
    {{
        return {model}::class;
    }}

    public static function findById(int $id): ?{model}
    {{
        return static::model()::query()->find($id);
    }}
{slug_find}
    public static function storeByRequest(array $data): {model}
    {{{strip}
        $record = static::model()::query()->create($data);
{trans}
        return $record->fresh();
    }}

    public static function updateByRequest({model} $record, array $data): bool
    {{{strip}
        $updated = $record->update($data);
{trans}
        return $updated;
    }}

    public static function delete({model} $record): bool
    {{
        return (bool) $record->delete();
    }}
}}
"""


repo("CourseCategory", "CourseCategory", """use App\\Services\\TranslationService;
use Illuminate\\Support\\Str;

class CourseCategoryRepository
{
    public static function model()
    {
        return CourseCategory::class;
    }

    public static function findById(int $id): ?CourseCategory
    {
        return static::model()::query()->find($id);
    }

    public static function findBySlug(string $slug): ?CourseCategory
    {
        return static::model()::query()->where('slug', $slug)->first();
    }

    public static function storeByRequest(array $data): CourseCategory
    {
        $data['slug'] = CourseCategory::generateUniqueSlug(
            $data['translations']['en']['name'] ?? $data['slug'] ?? Str::random(8)
        );

        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $category = static::model()::query()->create($data);

        if (! empty($translations)) {
            TranslationService::sync($category, 'translations', $translations);
        }

        return $category->fresh();
    }

    public static function updateByRequest(CourseCategory $category, array $data): bool
    {
        if (! empty($data['translations']['en']['name'])) {
            $data['slug'] = CourseCategory::generateUniqueSlug($data['translations']['en']['name'], $category->id);
        }

        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $updated = $category->update($data);

        if (! empty($translations)) {
            TranslationService::sync($category, 'translations', $translations);
        }

        return $updated;
    }
}
""")

repo("Course", "Course", """use App\\Repositories\\MediaRepository;
use Illuminate\\Support\\Str;

class CourseRepository
{
    public static function model()
    {
        return Course::class;
    }

    public static function findById(int $id): ?Course
    {
        return static::model()::query()->find($id);
    }

    public static function findBySlug(string $slug): ?Course
    {
        return static::model()::query()->where('slug', $slug)->first();
    }

    public static function storeByRequest(array $data): Course
    {
        $data['slug'] = Course::generateUniqueSlug($data['title'] ?? Str::random(8));

        $relations = $data;
        unset($data['level_ids'], $data['language_ids'], $data['medias']);

        $course = static::model()::query()->create($data);

        $data = $relations;

        static::syncRelations($course, $data);

        return $course->fresh();
    }

    public static function updateByRequest(Course $course, array $data): bool
    {
        if (! empty($data['title'])) {
            $data['slug'] = Course::generateUniqueSlug($data['title'], $course->id);
        }

        $relations = $data;
        unset($data['level_ids'], $data['language_ids'], $data['medias']);

        $updated = $course->update($data);

        static::syncRelations($course, $relations);

        return $updated;
    }

    protected static function syncRelations(Course $course, array $data): void
    {
        if (array_key_exists('level_ids', $data)) {
            $course->levels()->delete();
            foreach ((array) $data['level_ids'] as $levelId) {
                $course->levels()->create(['level_id' => $levelId]);
            }
        }

        if (array_key_exists('language_ids', $data)) {
            $course->languages()->delete();
            foreach ((array) $data['language_ids'] as $languageId) {
                $course->languages()->create(['language_id' => $languageId]);
            }
        }

        if (! empty($data['medias'])) {
            MediaRepository::syncForParent($course, $data['medias'], CourseMedia::class, 'course_id');
        }
    }
}
""".replace("use App\\Repositories\\MediaRepository;\n", "use App\\Models\\CourseMedia;\n"))

repo("Blog", "Blog", std_repo("Blog", slug=True, translatable="translations").replace(
    "        $record = static::model()::query()->create($data);",
    "        $data['slug'] = Blog::generateUniqueSlug($translations['en']['title'] ?? $data['slug'] ?? \\Illuminate\\Support\\Str::random(8));\n        $record = static::model()::query()->create($data);", 1))

repo("Order", "Order", """class OrderRepository
{
    public static function model()
    {
        return Order::class;
    }

    public static function findById(int $id): ?Order
    {
        return static::model()::query()->find($id);
    }

    public static function storeByRequest(array $data): Order
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $order = static::model()::query()->create($data);

        foreach ($items as $item) {
            $order->orderItems()->create($item);
        }

        return $order->fresh();
    }

    public static function updateByRequest(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    public static function updateStatus(Order $order, string $status): bool
    {
        return $order->update(['status' => $status]);
    }
}
""")

repo("Coupon", "Coupon", """class CouponRepository
{
    public static function model()
    {
        return Coupon::class;
    }

    public static function findById(int $id): ?Coupon
    {
        return static::model()::query()->find($id);
    }

    public static function findByCode(string $code): ?Coupon
    {
        return static::model()::query()->where('coupon_code', $code)->first();
    }

    public static function storeByRequest(array $data): Coupon
    {
        return static::model()::query()->create($data);
    }

    public static function updateByRequest(Coupon $coupon, array $data): bool
    {
        return $coupon->update($data);
    }
}
""")

repo("User", "User", """use App\\Enums\\UserRoleEnum;
use App\\Enums\\UserStatusEnum;

class UserRepository
{
    public static function model()
    {
        return User::class;
    }

    public static function findById(int $id): ?User
    {
        return static::model()::query()->find($id);
    }

    public static function findByEmail(string $email): ?User
    {
        return static::model()::query()->where('email', $email)->first();
    }

    public static function storeByRequest(array $data): User
    {
        return static::model()::query()->create($data);
    }

    public static function updateByRequest(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public static function updateStatus(User $user, string $status): bool
    {
        return $user->update(['status' => $status]);
    }

    public static function ban(User $user): bool
    {
        return $user->update(['is_banned' => 'yes']);
    }

    public static function unban(User $user): bool
    {
        return $user->update(['is_banned' => 'no']);
    }

    public static function makeInstructor(User $user): bool
    {
        return $user->update(['role' => UserRoleEnum::INSTRUCTOR->value]);
    }
}
""")

repo("Admin", "Admin", """class AdminRepository
{
    public static function model()
    {
        return Admin::class;
    }

    public static function findById(int $id): ?Admin
    {
        return static::model()::query()->find($id);
    }

    public static function findByEmail(string $email): ?Admin
    {
        return static::model()::query()->where('email', $email)->first();
    }

    public static function storeByRequest(array $data): Admin
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $admin = static::model()::query()->create($data);

        if (! empty($roles)) {
            $admin->assignRole($roles);
        }

        return $admin->fresh();
    }

    public static function updateByRequest(Admin $admin, array $data): bool
    {
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        $updated = $admin->update($data);

        if (is_array($roles)) {
            $admin->syncRoles($roles);
        }

        return $updated;
    }

    public static function updateStatus(Admin $admin, string $status): bool
    {
        // Callers must refuse when $admin->id === auth('admin')->id().
        return $admin->update(['status' => $status]);
    }
}
""")

repo("Instructor", "Instructor", std_repo("Instructor"))
repo("Testimonial", "Testimonial", std_repo("Testimonial", translatable="translations"))
repo("Faq", "Faq", std_repo("Faq", translatable="translations"))
repo("Brand", "Brand", std_repo("Brand"))
repo("Badge", "Badge", std_repo("Badge"))
repo("Language", "Language", """class LanguageRepository
{
    public static function model()
    {
        return Language::class;
    }

    public static function findById(int $id): ?Language
    {
        return static::model()::query()->find($id);
    }

    public static function findByCode(string $code): ?Language
    {
        return static::model()::query()->where('code', $code)->first();
    }

    public static function storeByRequest(array $data): Language
    {
        return static::model()::query()->create($data);
    }

    public static function updateByRequest(Language $language, array $data): bool
    {
        return $language->update($data);
    }

    public static function makeDefault(Language $language): bool
    {
        static::model()::query()->update(['is_default' => '0']);
        Cache::forget('defaultLanguage');

        return $language->update(['is_default' => '1']);
    }
}
""".replace("class LanguageRepository", "use Illuminate\\Support\\Facades\\Cache;\n\nclass LanguageRepository"))

repo("Menu", "Menu", std_repo("Menu", slug=True, translatable="translations"))
repo("Page", "CustomPage", """use App\\Services\\TranslationService;
use Illuminate\\Support\\Str;

class PageRepository
{
    public static function model()
    {
        return CustomPage::class;
    }

    public static function findById(int $id): ?CustomPage
    {
        return static::model()::query()->find($id);
    }

    public static function findBySlug(string $slug): ?CustomPage
    {
        return static::model()::query()->where('slug', $slug)->first();
    }

    public static function storeByRequest(array $data): CustomPage
    {
        $data['slug'] = CustomPage::generateUniqueSlug(
            $data['translations']['en']['name'] ?? $data['slug'] ?? Str::random(8)
        );

        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $page = static::model()::query()->create($data);

        if (! empty($translations)) {
            TranslationService::sync($page, 'translations', $translations);
        }

        return $page->fresh();
    }

    public static function updateByRequest(CustomPage $page, array $data): bool
    {
        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $updated = $page->update($data);

        if (! empty($translations)) {
            TranslationService::sync($page, 'translations', $translations);
        }

        return $updated;
    }
}
""")

repo("CertificateBuilder", "CertificateBuilder", """class CertificateBuilderRepository
{
    public static function model()
    {
        return CertificateBuilder::class;
    }

    public static function findById(int $id): ?CertificateBuilder
    {
        return static::model()::query()->find($id);
    }

    public static function storeByRequest(array $data): CertificateBuilder
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $builder = static::model()::query()->create($data);

        static::syncItems($items);

        return $builder->fresh();
    }

    public static function updateByRequest(CertificateBuilder $builder, array $data): bool
    {
        $items = $data['items'] ?? null;
        unset($data['items']);

        $updated = $builder->update($data);

        if (is_array($items)) {
            static::syncItems($items);
        }

        return $updated;
    }

    protected static function syncItems(array $items): void
    {
        // certificate_builder_items has no builder FK in the dump;
        // items are global positioned elements, replaced wholesale.
        CertificateBuilderItem::query()->delete();

        foreach ($items as $item) {
            CertificateBuilderItem::query()->create($item);
        }
    }
}
""".replace("class CertificateBuilderRepository",
            "use App\\Models\\CertificateBuilderItem;\n\nclass CertificateBuilderRepository"))

repo("Setting", "Setting", """use App\\Services\\SettingService;

class SettingRepository
{
    public static function model()
    {
        return Setting::class;
    }

    public static function findById(int $id): ?Setting
    {
        return static::model()::query()->find($id);
    }

    public static function findByKey(string $key): ?Setting
    {
        return static::model()::query()->where('key', $key)->first();
    }

    public static function storeByRequest(array $data): Setting
    {
        $setting = static::model()::query()->updateOrCreate(
            ['key' => $data['key']],
            ['value' => $data['value'] ?? '']
        );

        SettingService::forgetCache();

        return $setting;
    }

    public static function updateByRequest(Setting $setting, array $data): bool
    {
        $updated = $setting->update($data);

        SettingService::forgetCache();

        return $updated;
    }

    public static function updateMany(array $data): void
    {
        foreach ($data as $key => $value) {
            SettingService::set($key, $value);
        }
    }
}
""")

repo("Media", "Media", """use App\\Services\\ImageService;
use Illuminate\\Http\\UploadedFile;
use Illuminate\\Support\\Facades\\Storage;

class MediaRepository
{
    public static function model()
    {
        return Media::class;
    }

    public static function findById(int $id): ?Media
    {
        return static::model()::query()->find($id);
    }

    public static function storeByRequest(UploadedFile $file, string $path, ?string $type = null, ?string $folder = null, ?string $description = null): Media
    {
        [$upload, $optimizedPath] = ImageService::optimize($file);

        try {
            $storedPath = Storage::disk('public')->putFile($path, $optimizedPath ? new \\Illuminate\\Http\\File($optimizedPath) : $upload);
        } finally {
            if ($optimizedPath && file_exists($optimizedPath)) {
                unlink($optimizedPath);
            }
        }

        $extension = strtolower($file->getClientOriginalExtension());

        return static::model()::query()->create([
            'name' => $file->getClientOriginalName(),
            'src' => $storedPath,
            'type' => $type ?? (in_array($extension, ImageService::SUPPORTED_EXTENSIONS, true) ? 'image' : $extension),
            'extension' => $extension,
        ]);
    }

    public static function updateByRequest(UploadedFile $file, string $path, ?string $type, Media $media, ?string $folder = null): Media
    {
        $fresh = static::storeByRequest($file, $path, $type, $folder);

        static::deleteByMedia($media);

        return $fresh;
    }

    public static function updateOrCreateByRequest(UploadedFile $file, string $path, ?string $type, ?Media $media, ?string $folder = null): Media
    {
        if ($media) {
            return static::updateByRequest($file, $path, $type, $media, $folder);
        }

        return static::storeByRequest($file, $path, $type, $folder);
    }

    public static function deleteByMedia(Media $media): bool
    {
        if ($media->src) {
            Storage::disk('public')->delete($media->src);
        }

        return (bool) $media->delete();
    }

    public static function storeByFromUrlRequest(string $url, string $path, ?string $description = null): ?Media
    {
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return null;
        }

        $contents = @file_get_contents($url);

        if ($contents === false) {
            return null;
        }

        $name = basename(parse_url($url, PHP_URL_PATH) ?: 'remote-file');
        $storedPath = Storage::disk('public')->put($path.'/'.$name, $contents);

        if (! $storedPath) {
            return null;
        }

        return static::model()::query()->create([
            'name' => $name,
            'src' => $storedPath,
            'type' => 'image',
            'extension' => strtolower(pathinfo($name, PATHINFO_EXTENSION)),
        ]);
    }

    public static function optimizeExisting(Media $media, bool $force = false): bool
    {
        return false;
    }

    /**
     * Sync a parent model's pivot media rows (course_media, blog_media, ...).
     * $medias: [['file' => UploadedFile, 'is_primary' => bool], ...]
     * $existingMediaIds: pivot media_ids to keep; others are removed.
     */
    public static function syncForParent(Model $parent, array $medias, string $pivotClass, string $parentKey, ?array $existingMediaIds = null): void
    {
        $order = (int) $pivotClass::query()->where($parentKey, $parent->id)->max('order');

        foreach ($medias as $item) {
            $file = $item['file'] ?? null;

            if (! $file instanceof UploadedFile) {
                continue;
            }

            $media = static::storeByRequest($file, strtolower(class_basename($parent)), 'image');

            $pivotClass::query()->create([
                $parentKey => $parent->id,
                'media_id' => $media->id,
                'is_primary' => (bool) ($item['is_primary'] ?? false),
                'order' => ++$order,
            ]);
        }

        if (is_array($existingMediaIds)) {
            $pivotClass::query()
                ->where($parentKey, $parent->id)
                ->whereNotIn('media_id', $existingMediaIds)
                ->delete();
        }
    }
}
""".replace("class MediaRepository", "use Illuminate\\Database\\Eloquent\\Model;\n\nclass MediaRepository"))

# ---------------------------------------------------------------- services
SERVICES: dict[str, str] = {}

SERVICES["ImageService.php"] = """<?php

namespace App\\Services;

use Illuminate\\Http\\UploadedFile;
use Intervention\\Image\\ImageManager;
use Intervention\\Image\\Drivers\\Gd\\Driver as GdDriver;
use Intervention\\Image\\Drivers\\Imagick\\Driver as ImagickDriver;

class ImageService
{
    public const MAX_DIMENSION = 1280;

    public const WEBP_QUALITY = 80;

    public const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public static function isSupported(UploadedFile $file): bool
    {
        return in_array(strtolower($file->getClientOriginalExtension()), static::SUPPORTED_EXTENSIONS, true);
    }

    protected static function manager(): ImageManager
    {
        $driver = config('images.driver', 'gd') === 'imagick'
            ? new ImagickDriver
            : new GdDriver;

        return new ImageManager($driver);
    }

    /**
     * Optimise an uploaded image to WebP (max 1280px on the longest
     * side, quality 80). Returns [$originalFile, $optimizedTempPath|null].
     * Non-supported files pass through untouched; the caller must
     * unlink the temp file (MediaRepository does this in a finally).
     *
     * @return array{0: UploadedFile, 1: string|null}
     */
    public static function optimize(UploadedFile $file): array
    {
        if (! static::isSupported($file)) {
            return [$file, null];
        }

        $max = (int) config('images.max_dimension', static::MAX_DIMENSION);
        $quality = (int) config('images.quality', static::WEBP_QUALITY);

        $image = static::manager()->read($file->getRealPath());

        $image->scale(width: $max, height: $max);

        $tempPath = tempnam(sys_get_temp_dir(), 'img_').'.webp';

        $image->toWebp($quality)->save($tempPath);

        return [$file, $tempPath];
    }
}
"""

SERVICES["SettingService.php"] = """<?php

namespace App\\Services;

use App\\Models\\Setting;
use Illuminate\\Support\\Facades\\Cache;

class SettingService
{
    public const CACHE_KEY = 'site_settings';

    public static function all(): array
    {
        return Cache::rememberForever(static::CACHE_KEY, function () {
            return Setting::query()->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): Setting
    {
        $setting = Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_string($value) ? $value : (string) $value]
        );

        static::forgetCache();

        return $setting;
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }
}
"""

SERVICES["PaymentService.php"] = """<?php

namespace App\\Services;

use App\\Models\\BasicPayment;
use App\\Models\\Order;
use App\\Models\\PaymentGateway;

class PaymentService
{
    /**
     * Gateways present (by key prefix) in the old payment_gateways /
     * basic_payments tables. Values are NEVER hard-coded here: every
     * credential comes from the database or env() placeholders.
     */
    public const GATEWAYS = [
        'stripe', 'paypal', 'razorpay', 'flutterwave', 'paystack',
        'mollie', 'instamojo', 'uddoktapay', 'bank',
    ];

    public static function gatewayConfig(string $gateway): array
    {
        $prefix = strtolower($gateway).'_';

        $config = PaymentGateway::query()
            ->where('key', 'like', $prefix.'%')
            ->pluck('value', 'key')
            ->all();

        $basic = BasicPayment::query()
            ->where('key', 'like', $prefix.'%')
            ->pluck('value', 'key')
            ->all();

        return array_merge($basic, $config);
    }

    public static function isEnabled(string $gateway): bool
    {
        $config = static::gatewayConfig($gateway);

        $status = $config[strtolower($gateway).'_status']
            ?? $config[strtolower($gateway).'_active']
            ?? null;

        return in_array($status, ['1', 'active', 'enabled', true, 1], true);
    }

    public static function supportedGateways(): array
    {
        return array_values(array_filter(
            static::GATEWAYS,
            fn ($gateway) => static::isEnabled($gateway)
        ));
    }

    /**
     * Stub: resolves the gateway config and returns what a checkout
     * would need. No real charge is attempted here -- the concrete
     * gateway driver (Stripe/PayPal/...) is wired in when its SDK
     * and credentials are configured.
     */
    public static function createPayment(Order $order, string $gateway): array
    {
        if (! in_array(strtolower($gateway), static::GATEWAYS, true)) {
            return ['status' => 'error', 'message' => 'Unsupported payment gateway.'];
        }

        if (! static::isEnabled($gateway)) {
            return ['status' => 'not_configured', 'message' => 'Payment gateway is not enabled.'];
        }

        return [
            'status' => 'pending',
            'gateway' => $gateway,
            'order_id' => $order->id,
            'amount' => $order->payable_with_charge ?? $order->payable_amount,
            'currency' => $order->payable_currency,
            // 'redirect_url' / 'transaction_id' are filled in by the gateway driver.
        ];
    }
}
"""

SERVICES["CertificateService.php"] = """<?php

namespace App\\Services;

use App\\Models\\CertificateBuilder;
use App\\Models\\Course;
use App\\Models\\User;

class CertificateService
{
    public static function builder(): ?CertificateBuilder
    {
        return CertificateBuilder::query()->first();
    }

    public static function buildHtml(Course $course, User $user): string
    {
        $builder = static::builder();

        $title = $builder?->title ?? config('app.name');
        $subTitle = $builder?->sub_title ?? '';
        $description = $builder?->description ?? '';

        $items = $builder?->builderItems() ?? collect();

        $positions = $items->mapWithKeys(fn ($item) => [
            $item->element_id => ['x' => $item->x_position, 'y' => $item->y_position],
        ])->all();

        // Positions are percentages of the certificate canvas; the
        // Blade/PDF view reads $positions[$elementId] per element.
        return view('certificates.certificate', [
            'course' => $course,
            'user' => $user,
            'builder' => $builder,
            'title' => $title,
            'subTitle' => $subTitle,
            'description' => $description,
            'positions' => $positions,
        ])->render();
    }

    /**
     * Render the certificate as a PDF when barryvdh/laravel-dompdf
     * is installed; otherwise return the HTML so the caller can
     * still preview it. (dompdf is not a required dependency yet.)
     */
    public static function download(Course $course, User $user): string
    {
        $html = static::buildHtml($course, $user);

        if (class_exists(\\Barryvdh\\DomPDF\\Facade\\Pdf::class)) {
            return \\Barryvdh\\DomPDF\\Facade\\Pdf::loadHtml($html)
                ->setPaper('a4', 'landscape')
                ->output();
        }

        return $html;
    }
}
"""

SERVICES["TranslationService.php"] = """<?php

namespace App\\Services;

use App\\Models\\Language;
use Illuminate\\Database\\Eloquent\\Model;

class TranslationService
{
    public static function supportedCodes(): array
    {
        $codes = Language::query()->where('status', '1')->pluck('code')->all();

        return $codes !== [] ? $codes : ['en', 'bn'];
    }

    /**
     * Sync translation rows for a parent model.
     *
     * $translations shape:
     *   ['en' => ['title' => '...', ...], 'bn' => ['title' => '...', ...]]
     * $relation is the HasMany translations relation name.
     */
    public static function sync(Model $model, string $relation, array $translations): void
    {
        $relationQuery = $model->{$relation}();

        foreach ($translations as $langCode => $fields) {
            if (! is_array($fields) || $fields === []) {
                continue;
            }

            $foreignKey = $relationQuery->getForeignKeyName();

            $relationQuery->updateOrCreate(
                ['lang_code' => $langCode],
                array_merge($fields, [$foreignKey => $model->getKey()])
            );
        }
    }

    public static function get(Model $model, string $relation, string $langCode): ?Model
    {
        return $model->{$relation}()->where('lang_code', $langCode)->first();
    }
}
"""

# ---------------------------------------------------------------- trait
PERMISSIONS_TRAIT = """<?php

namespace App\\Traits;

use ReflectionClass;

trait PermissionsTrait
{
    public static array $dashboardPermissions = [
        'group_name' => 'dashboard',
        'permissions' => [
            'dashboard.view',
        ],
    ];

    public static array $adminProfilePermissions = [
        'group_name' => 'admin profile',
        'permissions' => [
            'admin.profile.view',
            'admin.profile.update',
        ],
    ];

    public static array $adminPermissions = [
        'group_name' => 'admin',
        'permissions' => [
            'admin.view',
            'admin.create',
            'admin.store',
            'admin.edit',
            'admin.update',
            'admin.delete',
        ],
    ];

    public static array $blogCatgoryPermissions = [
        'group_name' => 'blog category',
        'permissions' => [
            'blog.category.view',
            'blog.category.create',
            'blog.category.translate',
            'blog.category.store',
            'blog.category.edit',
            'blog.category.update',
            'blog.category.delete',
        ],
    ];

    public static array $blogPermissions = [
        'group_name' => 'blog',
        'permissions' => [
            'blog.view',
            'blog.create',
            'blog.translate',
            'blog.store',
            'blog.edit',
            'blog.update',
            'blog.delete',
        ],
    ];

    public static array $blogCommentPermissions = [
        'group_name' => 'blog comment',
        'permissions' => [
            'blog.comment.view',
            'blog.comment.update',
            'blog.comment.delete',
        ],
    ];

    public static array $rolePermissions = [
        'group_name' => 'role',
        'permissions' => [
            'role.view',
            'role.create',
            'role.store',
            'role.assign',
            'role.edit',
            'role.update',
            'role.delete',
        ],
    ];

    public static array $settingPermissions = [
        'group_name' => 'setting',
        'permissions' => [
            'setting.view',
            'setting.update',
        ],
    ];

    public static array $basicPaymentPermissions = [
        'group_name' => 'basic payment',
        'permissions' => [
            'basic.payment.view',
            'basic.payment.update',
        ],
    ];

    public static array $contectMessagePermissions = [
        'group_name' => 'contect message',
        'permissions' => [
            'contect.message.view',
            'contect.message.delete',
        ],
    ];

    public static array $currencyPermissions = [
        'group_name' => 'currency',
        'permissions' => [
            'currency.view',
            'currency.create',
            'currency.store',
            'currency.edit',
            'currency.update',
            'currency.delete',
        ],
    ];

    public static array $customerPermissions = [
        'group_name' => 'customer',
        'permissions' => [
            'customer.view',
            'customer.bulk.mail',
            'customer.create',
            'customer.store',
            'customer.edit',
            'customer.update',
            'customer.delete',
        ],
    ];

    public static array $languagePermissions = [
        'group_name' => 'language',
        'permissions' => [
            'language.view',
            'language.create',
            'language.store',
            'language.edit',
            'language.update',
            'language.delete',
            'language.translate',
            'language.single.translate',
        ],
    ];

    public static array $menuPermissions = [
        'group_name' => 'menu builder',
        'permissions' => [
            'menu.view',
            'menu.create',
            'menu.store',
            'menu.edit',
            'menu.update',
            'menu.delete',
        ],
    ];

    public static array $pagePermissions = [
        'group_name' => 'page builder',
        'permissions' => [
            'page.management',
        ],
    ];

    public static array $paymentPermissions = [
        'group_name' => 'payment',
        'permissions' => [
            'payment.view',
            'payment.update',
        ],
    ];

    public static array $newsletterPermissions = [
        'group_name' => 'newsletter',
        'permissions' => [
            'newsletter.view',
            'newsletter.mail',
            'newsletter.delete',
        ],
    ];

    public static array $testimonialPermissions = [
        'group_name' => 'testimonial',
        'permissions' => [
            'testimonial.view',
            'testimonial.create',
            'testimonial.translate',
            'testimonial.store',
            'testimonial.edit',
            'testimonial.update',
            'testimonial.delete',
        ],
    ];

    public static array $faqPermissions = [
        'group_name' => 'faq',
        'permissions' => [
            'faq.view',
            'faq.create',
            'faq.translate',
            'faq.store',
            'faq.edit',
            'faq.update',
            'faq.delete',
        ],
    ];

    public static array $locationPermissions = [
        'group_name' => 'locations',
        'permissions' => [
            'location.view',
            'location.create',
            'location.store',
            'location.edit',
            'location.update',
            'location.delete',
        ],
    ];

    public static array $instructorRequestPermissions = [
        'group_name' => 'instructor request',
        'permissions' => [
            'instructor.request.list',
            'instructor.request.setting',
        ],
    ];

    public static array $coursePermissions = [
        'group_name' => 'courses',
        'permissions' => [
            'course.management',
        ],
    ];

    public static array $CertificatePermission = [
        'group_name' => 'course certificate management',
        'permissions' => [
            'course.certificate.management',
        ],
    ];

    public static array $badgePermission = [
        'group_name' => 'Badges',
        'permissions' => [
            'badge.management',
        ],
    ];

    public static array $OrderPermission = [
        'group_name' => 'order management',
        'permissions' => [
            'order.management',
        ],
    ];

    public static array $couponPermission = [
        'group_name' => 'coupon management',
        'permissions' => [
            'coupon.management',
        ],
    ];

    public static array $withdrawPermission = [
        'group_name' => 'withdraw management',
        'permissions' => [
            'withdraw.management',
        ],
    ];

    public static array $appearancePermission = [
        'group_name' => 'site appearance management',
        'permissions' => [
            'appearance.management',
        ],
    ];

    public static array $siteSectionPermission = [
        'group_name' => 'site appearance management',
        'permissions' => [
            'section.management',
        ],
    ];

    public static array $brandPermission = [
        'group_name' => 'brand management',
        'permissions' => [
            'brand.management',
        ],
    ];

    public static array $footerPermission = [
        'group_name' => 'footer management',
        'permissions' => [
            'footer.management',
        ],
    ];

    public static array $socialPermission = [
        'group_name' => 'social link management',
        'permissions' => [
            'social.link.management',
        ],
    ];

    private static function getSuperAdminPermissions(): array
    {
        $reflection = new ReflectionClass(__TRAIT__);
        $properties = $reflection->getStaticProperties();

        $permissions = [];
        foreach ($properties as $value) {
            if (is_array($value)) {
                $permissions[] = [
                    'group_name' => $value['group_name'],
                    'permissions' => (array) $value['permissions'],
                ];
            }
        }

        return $permissions;
    }
}
"""

HELPERS = """<?php

use App\\Models\\Language;
use App\\Models\\MultiCurrency;
use App\\Models\\Setting;
use App\\Services\\SettingService;
use Illuminate\\Support\\Facades\\Cache;
use Illuminate\\Support\\Facades\\Session;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return SettingService::get($key, $default);
    }
}

if (! function_exists('setSettingValue')) {
    function setSettingValue(string $key, mixed $value): Setting
    {
        return SettingService::set($key, $value);
    }
}

if (! function_exists('getSessionLanguage')) {
    function getSessionLanguage(): string
    {
        if (! session()->has('lang')) {
            session()->put('lang', config('app.locale'));
            session()->forget('text_direction');
            session()->put('text_direction', 'ltr');
        }

        return (string) Session::get('lang');
    }
}

if (! function_exists('getDefaultLanguage')) {
    function getDefaultLanguage(): string
    {
        $defaultLanguage = Cache::rememberForever('defaultLanguage', function () {
            try {
                return Language::query()->where('is_default', 1)->first()->code;
            } catch (\\Throwable $e) {
                info($e->getMessage());

                return 'en';
            }
        });

        return $defaultLanguage;
    }
}

if (! function_exists('allCurrencies')) {
    function allCurrencies()
    {
        $allCurrencies = Cache::rememberForever('allCurrencies', function () {
            return MultiCurrency::all();
        });

        if (! $allCurrencies) {
            $allCurrencies = MultiCurrency::all();
        }

        return $allCurrencies;
    }
}

if (! function_exists('getSessionCurrency')) {
    function getSessionCurrency(): string
    {
        if (! session()->has('currency_code') || ! session()->has('currency_rate') || ! session()->has('currency_position')) {
            $currency = allCurrencies()->where('is_default', 'yes')->first();

            if ($currency) {
                session()->put('currency_code', $currency->currency_code);
                session()->forget('currency_position');
                session()->put('currency_position', $currency->currency_position);
                session()->forget('currency_icon');
                session()->put('currency_icon', $currency->currency_icon);
                session()->forget('currency_rate');
                session()->put('currency_rate', $currency->currency_rate);
            }
        }

        return (string) session('currency_code', config('app.currency', 'BDT'));
    }
}
"""

IMAGES_CONFIG = """<?php

return [
    'driver' => env('IMAGE_DRIVER', 'gd'),
    'max_dimension' => env('IMAGE_MAX_DIMENSION', 1280),
    'quality' => env('IMAGE_QUALITY', 80),
];
"""


# ---------------------------------------------------------------- main
def check_braces(path: str) -> list[str]:
    """Naive brace/paren balance check (PHP has no php -l here)."""
    src = open(path, encoding="utf-8").read()
    # Strip strings and comments roughly so braces inside them don't count.
    src = re.sub(r"/\*.*?\*/", "", src, flags=re.S)
    src = re.sub(r"'(?:[^'\\]|\\.)*'", "''", src)
    src = re.sub(r'"(?:[^"\\]|\\.)*"', '""', src)
    src = re.sub(r"//.*", "", src)
    problems = []
    for a, b, name in [("{", "}", "braces"), ("(", ")", "parens"), ("[", "]", "brackets")]:
        if src.count(a) != src.count(b):
            problems.append(f"{name}: {src.count(a)} '{a}' vs {src.count(b)} '{b}'")
    # Duplicate method names in one file.
    methods = re.findall(r"function\s+(\w+)\s*\(", src)
    dupes = sorted({m for m in methods if methods.count(m) > 1})
    if dupes:
        problems.append(f"duplicate methods: {', '.join(dupes)}")
    return problems


def main() -> None:
    counts: dict[str, int] = {}

    for name, cases in ENUMS.items():
        write(os.path.join(APP, "Enums", f"{name}.php"), render_enum(name, cases))
    counts["enums"] = len(ENUMS)

    for spec in MODELS:
        write(os.path.join(APP, "Models", f"{spec['name']}.php"), render_model(spec))
    counts["models"] = len(MODELS)

    for fname, content in REPOS.items():
        write(os.path.join(APP, "Repositories", fname), content)
    counts["repositories"] = len(REPOS)

    for fname, content in SERVICES.items():
        write(os.path.join(APP, "Services", fname), content)
    counts["services"] = len(SERVICES)

    write(os.path.join(APP, "Traits", "PermissionsTrait.php"), PERMISSIONS_TRAIT)
    write(os.path.join(APP, "helpers.php"), HELPERS)
    write(os.path.join(BUILD, "config", "images.php"), IMAGES_CONFIG)

    problems = []
    for root, _dirs, files in os.walk(APP):
        for f in files:
            if f.endswith(".php"):
                p = os.path.join(root, f)
                for prob in check_braces(p):
                    problems.append(f"{p}: {prob}")
    for prob in check_braces(os.path.join(BUILD, "config", "images.php")):
        problems.append(f"config/images.php: {prob}")

    print(counts)
    print("models:", sorted(s["name"] for s in MODELS))
    if problems:
        print("PROBLEMS:")
        for prob in problems:
            print(" -", prob)
        sys.exit(1)
    print("OK: all files brace-balanced, no duplicate methods.")


if __name__ == "__main__":
    main()
