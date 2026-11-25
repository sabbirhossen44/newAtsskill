<?php

namespace App\Repositories;

use Arafat\LaravelRepository\Repository;

use App\Models\Enrollment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EnrollmentRepository extends Repository
{
    public static function model()
    {
        return Enrollment::class;
    }

    public static function countForUser(int $userId): int
    {
        return static::model()::query()->where('user_id', $userId)->count();
    }

    public static function paginateForUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return static::model()::query()
            ->with(['course' => function ($query) {
                $query->withTrashed();
            }])
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
