<?php

namespace App\Repositories;

use App\Models\SocialiteCredential;

class SocialiteCredentialRepository
{
    public static function model()
    {
        return SocialiteCredential::class;
    }

    public static function findById(int $id): ?SocialiteCredential
    {
        return static::model()::query()->find($id);
    }

    public static function findForUser(int $userId, string $providerName, string $providerId): ?SocialiteCredential
    {
        return static::model()::query()
            ->where('user_id', $userId)
            ->where(['provider_name' => $providerName, 'provider_id' => $providerId])
            ->first();
    }

    public static function storeByRequest(int $userId, array $data): SocialiteCredential
    {
        return static::model()::query()->create(array_merge($data, [
            'user_id' => $userId,
        ]));
    }
}
