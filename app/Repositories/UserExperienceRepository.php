<?php

namespace App\Repositories;

use Arafat\LaravelRepository\Repository;

use App\Models\UserExperience;
use Illuminate\Database\Eloquent\Collection;

class UserExperienceRepository extends Repository
{
    public static function model()
    {
        return UserExperience::class;
    }

    public static function findById(int $id): ?UserExperience
    {
        return self::find($id);
    }

    public static function allForUser(int $userId): Collection
    {
        return static::model()::query()->where('user_id', $userId)->get();
    }

    public static function storeByRequest(int $userId, array $data): UserExperience
    {
        return self::create(array_merge($data, [
            'user_id' => $userId,
        ]));
    }

    public static function updateByRequest(UserExperience $experience, array $data): bool
    {
        return $experience->update($data);
    }

    public static function findForUserOrFail(int $id, int $userId): UserExperience
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
