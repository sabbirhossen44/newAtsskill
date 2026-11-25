<?php

namespace App\Models;

use App\Enums\QuizQuestionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => \App\Enums\QuizQuestionTypeEnum::class,
        ];
    }

    public function quiz(): BelongsTo
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

}
