<?php

namespace App\Repositories;

use App\Models\User;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;

class UserRepository
{
    public static function model()
    {
        return User::class;
    }

    public static function findById(int $id): ?User
    {
        return static::model()::query()->find($id);
    }

    public static function findByEmail(string $email): ?User
    {
        return static::model()::query()->where('email', $email)->first();
    }

    public static function storeByRequest(array $data): User
    {
        return static::model()::query()->create($data);
    }

    public static function updateByRequest(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public static function updateStatus(User $user, string $status): bool
    {
        return $user->update(['status' => $status]);
    }

    public static function ban(User $user): bool
    {
        return $user->update(['is_banned' => 'yes']);
    }

    public static function unban(User $user): bool
    {
        return $user->update(['is_banned' => 'no']);
    }

    public static function makeInstructor(User $user): bool
    {
        return $user->update(['role' => UserRoleEnum::INSTRUCTOR->value]);
    }
}
