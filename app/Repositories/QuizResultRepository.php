<?php

namespace App\Repositories;

use Arafat\LaravelRepository\Repository;

use App\Models\QuizResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class QuizResultRepository extends Repository
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
        return self::create($data);
    }

    public static function findByIdOrFail(int $id): QuizResult
    {
        return self::findOrFail($id);
    }
}
