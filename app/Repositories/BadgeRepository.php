<?php

namespace App\Repositories;

use App\Models\Badge;
use App\Services\TranslationService;

class BadgeRepository
{
    public static function model()
    {
        return Badge::class;
    }

    public static function findById(int $id): ?Badge
    {
        return static::model()::query()->find($id);
    }

    public static function storeByRequest(array $data): Badge
    {
        $record = static::model()::query()->create($data);

        return $record->fresh();
    }

    public static function updateByRequest(Badge $record, array $data): bool
    {
        $updated = $record->update($data);

        return $updated;
    }

    public static function delete(Badge $record): bool
    {
        return (bool) $record->delete();
    }
}
