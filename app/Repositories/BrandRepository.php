<?php

namespace App\Repositories;

use App\Models\Brand;
use App\Services\TranslationService;

class BrandRepository
{
    public static function model()
    {
        return Brand::class;
    }

    public static function findById(int $id): ?Brand
    {
        return static::model()::query()->find($id);
    }

    public static function storeByRequest(array $data): Brand
    {
        $record = static::model()::query()->create($data);

        return $record->fresh();
    }

    public static function updateByRequest(Brand $record, array $data): bool
    {
        $updated = $record->update($data);

        return $updated;
    }

    public static function delete(Brand $record): bool
    {
        return (bool) $record->delete();
    }
}
