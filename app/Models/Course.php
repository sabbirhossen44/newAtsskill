<?php

namespace App\Models;

use App\Enums\CourseApprovalEnum;
use App\Enums\CourseStatusEnum;
use App\Enums\CourseTypeEnum;
use App\Enums\LessonStorageEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'price' => 'double',
            'discount' => 'double',
            'certificate' => 'boolean',
            'downloadable' => 'boolean',
            'partner_instructor' => 'boolean',
            'qna' => 'boolean',
            'type' => \App\Enums\CourseTypeEnum::class,
            'status' => \App\Enums\CourseStatusEnum::class,
            'is_approved' => \App\Enums\CourseApprovalEnum::class,
            'demo_video_storage' => \App\Enums\LessonStorageEnum::class,
        ];
    }

    public function scopeActive(Builder $query): Builder
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

    public static function generateUniqueSlug(string $title, ?int $acceptId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while ((static::withTrashed())->where('slug', $slug)->where('id', '!=', $acceptId)->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

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

}
