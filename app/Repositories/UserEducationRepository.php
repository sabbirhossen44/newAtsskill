<?php

namespace App\Repositories;

use Arafat\LaravelRepository\Repository;

use App\Models\UserEducation;
use Illuminate\Database\Eloquent\Collection;

class UserEducationRepository extends Repository
{
    public static function model()
    {
        return UserEducation::class;
    }

    public static function findById(int $id): ?UserEducation
    {
        return self::find($id);
    }

    public static function allForUser(int $userId): Collection
    {
        return static::model()::query()->where('user_id', $userId)->get();
    }

    public static function storeByRequest(int $userId, array $data): UserEducation
    {
        return self::create(array_merge($data, [
            'user_id' => $userId,
        ]));
    }

    public static function updateByRequest(UserEducation $education, array $data): bool
    {
        return $education->update($data);
    }

    public static function findForUserOrFail(int $id, int $userId): UserEducation
    {
        return static::model()::query()
            ->whereUserId($userId)
            ->whereId($id)
            ->firstOrFail();
    }

    public static function deleteForUser(int $id, int $userId): bool
    {
        return (bool) static::findForUserOrFail($id, $userId)->delete();
    }
}
