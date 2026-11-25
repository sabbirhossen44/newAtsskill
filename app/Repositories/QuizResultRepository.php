<?php

namespace App\Repositories;

use App\Models\QuizResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class QuizResultRepository
{
    public static function model()
    {
        return QuizResult::class;
    }

    public static function countForUser(int $userId): int
    {
        return static::model()::query()->where('user_id', $userId)->count();
    }

    public static function paginateForUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return static::model()::query()
            ->with(['quiz'])
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public static function countAttempts(int $userId, int $quizId): int
    {
        return static::model()::query()->where('user_id', $userId)->where('quiz_id', $quizId)->count();
    }

    public static function storeByRequest(array $data): QuizResult
    {
        return static::model()::query()->create($data);
    }

    public static function findByIdOrFail(int $id): QuizResult
    {
        return static::model()::query()->findOrFail($id);
    }
}
