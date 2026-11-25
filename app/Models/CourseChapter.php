<?php

namespace App\Models;

use App\Enums\ChapterStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseChapter extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\ChapterStatusEnum::class,
        ];
    }

    public function course(): BelongsTo
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

}
