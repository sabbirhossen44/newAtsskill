<?php

namespace App\Repositories;

use App\Models\LessonQuestion;
use App\Services\TinymceImageService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LessonQuestionRepository
{
    public static function model()
    {
        return LessonQuestion::class;
    }

    public static function storeByRequest(array $data): LessonQuestion
    {
        return static::model()::query()->create([
            'user_id' => $data['user_id'],
            'lesson_id' => $data['lesson_id'],
            'course_id' => $data['course_id'],
            'question_title' => $data['question_title'],
            'question_description' => $data['question_description'],
        ]);
    }

    public static function paginateForLesson(Request $request): LengthAwarePaginator
    {
        $query = static::model()::query();
        $query->withCount('replies');

        $query->when($request->has('query') && strlen($requestQuery = $request->query('query')) > 0, function ($q) use ($requestQuery) {
            $q->where('question_title', 'like', '%'.$requestQuery.'%');
        });
        $query->when($request->filled('filter') && $request->filter == 'current_lecture', function ($q) use ($request) {
            $q->where('lesson_id', $request->lesson_id);
        });
        $query->where('course_id', $request->course_id);

        return $query->paginate(8, ['*'], 'page', $request->page ?: 1);
    }

    public static function findWithUser(int $id): ?LessonQuestion
    {
        return static::model()::query()->with('user')->where('id', $id)->first();
    }

    public static function deleteOwnedByUser(string $id, int $userId): bool
    {
        $question = static::model()::query()->where(['user_id' => $userId, 'id' => $id])->first();

        if (! $question) {
            return false;
        }

        $question->replies()->delete();
        TinymceImageService::deleteEmbeddedImages($question->question_description);
        $question->delete();

        return true;
    }
}
