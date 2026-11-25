<?php

namespace App\Models;

use App\Enums\ChapterItemTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseChapterItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => \App\Enums\ChapterItemTypeEnum::class,
        ];
    }

    public function chapter(): BelongsTo
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

}
