<?php

namespace App\Models;

use App\Enums\ChapterStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class CourseChapterLesson extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'downloadable' => 'boolean',
            'is_free' => 'boolean',
        ];
    }

    public function course(): BelongsTo
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

}
