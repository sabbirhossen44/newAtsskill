<?php

namespace App\Models;

use App\Enums\ChapterStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => App\Enums\ChapterStatusEnum::class,
        ];
    }

    public function chapterItem(): BelongsTo
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

}
