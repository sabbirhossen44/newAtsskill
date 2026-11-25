<?php

namespace App\Repositories;

use App\Models\LessonQuestion;
use App\Models\LessonReply;
use App\Services\TinymceImageService;
use Illuminate\Support\Collection;

class LessonReplyRepository
{
    public static function model()
    {
        return LessonReply::class;
    }

    public static function storeByRequest(array $data): LessonReply
    {
        $reply = static::model()::query()->create([
            'user_id' => $data['user_id'],
            'question_id' => $data['question_id'],
            'reply' => $data['reply'],
        ]);

        $lesson_question = LessonQuestion::find($data['question_id']);

        if ($lesson_question && \App\Models\Course::where('id', $lesson_question->course_id)
                ->where('instructor_id', '<>', $data['user_id'])
                ->exists()) {
            $lesson_question->update(['seen' => false]);
        }

        return $reply;
    }

    public static function forQuestion(int $questionId): Collection
    {
        return static::model()::query()->where('question_id', $questionId)->get();
    }

    public static function deleteOwnedByUser(string $id, int $userId): bool
    {
        $reply = static::model()::query()->where(['user_id' => $userId, 'id' => $id])->first();

        if (! $reply) {
            return false;
        }

        TinymceImageService::deleteEmbeddedImages($reply->reply);
        $reply->delete();

        return true;
    }
}
