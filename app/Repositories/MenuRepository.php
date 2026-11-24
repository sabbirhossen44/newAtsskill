<?php

namespace App\Repositories;

use App\Models\Menu;
use App\Services\TranslationService;

class MenuRepository
{
    public static function model()
    {
        return Menu::class;
    }

    public static function findById(int $id): ?Menu
    {
        return static::model()::query()->find($id);
    }

    public static function findBySlug(string $slug): ?Menu
    {
        return static::model()::query()->where('slug', $slug)->first();
    }

    public static function storeByRequest(array $data): Menu
    {
        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $record = static::model()::query()->create($data);

        if (! empty($translations) && is_array($translations)) {
            TranslationService::sync($record, 'translations', $translations);
        }

        return $record->fresh();
    }

    public static function updateByRequest(Menu $record, array $data): bool
    {
        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $updated = $record->update($data);

        if (! empty($translations) && is_array($translations)) {
            TranslationService::sync($record, 'translations', $translations);
        }

        return $updated;
    }

    public static function delete(Menu $record): bool
    {
        return (bool) $record->delete();
    }
}
