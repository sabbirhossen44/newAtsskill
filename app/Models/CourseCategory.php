<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class CourseCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'show_at_trending' => 'boolean',
        ];
    }

    public function translation(): HasOne
    {
        return $this->hasOne(CourseCategoryTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CourseCategoryTranslation::class, 'course_category_id');
    }

    public function getTranslation(string $code): ?CourseCategoryTranslation
    {
        return $this->hasOne(CourseCategoryTranslation::class)->where('lang_code', $code)->first();
    }

    public function getNameAttribute(): ?string
    {
        return $this->translation?->name ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->name ?: $this->translations()->first()?->name;
    }

    public function scopeActive(Builder $query): Builder
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
    public static function generateUniqueSlug(string $title, ?int $acceptId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while ((static::query())->where('slug', $slug)->where('id', '!=', $acceptId)->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

}
