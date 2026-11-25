<?php

namespace App\Repositories;

use Arafat\LaravelRepository\Repository;

use App\Services\AppMailService;
use App\Models\LessonQuestion;
use App\Models\LessonReply;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Instructor lesson Q&A (moved from InstructorLessonQnaController).
 * Reply mails go through AppMailService, as before.
 */
class LessonQnaRepository extends Repository
{
    public static function model()
    {
        return LessonQuestion::class;
    }

    public static function paginateQuestions(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = LessonQuestion::query();
        $query->select('id', 'user_id', 'seen', 'course_id', 'lesson_id', 'question_title', 'question_description', 'created_at')->with(['course' => function ($query) {
            $query->select('id', 'slug', 'title', 'thumbnail');
        }, 'lesson' => function ($query) {
            $query->select('id', 'chapter_item_id', 'title')->with(['chapterItem' => function ($query) {
                $query->select('id', 'type');
            }]);
        }, 'user' => function ($query) {
            $query->select('id', 'name', 'image');
        }, 'replies'])->whereHas('course', function ($query) {
            $query->whereNotNull('id');
        })->withCount('replies');

        $query->when(! empty($filters['seen']), function ($q) use ($filters) {
            $q->where('seen', $filters['seen']);
        });
        $orderBy = ! empty($filters['sort_by']) && $filters['sort_by'] == 1 ? 'asc' : 'desc';

        return $query->orderBy('id', $orderBy)->paginate($perPage)->withQueryString();
    }

    public static function destroyQuestion(string $id): bool
    {
        $question = LessonQuestion::where('id', $id)->first();
        if ($question) {
            $question->replies()->delete();
            extractAndFilterImageSrc($question?->question_description);
            $question->delete();

            return true;
        }

        return false;
    }

    public static function createReply(string $questionId, int $userId, string $replyText): LessonReply
    {
        $reply = LessonReply::create([
            'user_id' => $userId,
            'question_id' => $questionId,
            'reply' => $replyText,
        ]);
        $question = LessonQuestion::where('id', $questionId)->select('id', 'user_id', 'seen', 'course_id', 'lesson_id', 'question_title')->with(['course' => function ($query) {
            $query->select('id', 'slug', 'title');
        }, 'lesson' => function ($query) {
            $query->select('id', 'chapter_item_id', 'title')->with(['chapterItem' => function ($query) {
                $query->select('id', 'type');
            }]);
        }, 'user' => function ($query) {
            $query->select('id', 'name', 'email');
        }])->first();

        $question->update(['seen' => true]);

        AppMailService::sendQnaReplyMail($question);

        return $reply;
    }

    public static function destroyReply(string $id): bool
    {
        $reply = LessonReply::where('id', $id)->first();
        if ($reply) {
            extractAndFilterImageSrc($reply?->reply);
            $reply->delete();

            return true;
        }

        return false;
    }

    /** Toggle a question's seen flag; returns the updated question. */
    public static function toggleSeen(string $id): LessonQuestion
    {
        $question = LessonQuestion::find($id);
        $seen = $question->seen == 1 ? 0 : 1;
        $question->update(['seen' => $seen]);

        return $question;
    }
}
