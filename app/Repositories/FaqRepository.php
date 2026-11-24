<?php

namespace App\Repositories;

use App\Models\Faq;
use App\Services\TranslationService;

class FaqRepository
{
    public static function model()
    {
        return Faq::class;
    }

    public static function findById(int $id): ?Faq
    {
        return static::model()::query()->find($id);
    }

    public static function storeByRequest(array $data): Faq
    {
        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $record = static::model()::query()->create($data);

        if (! empty($translations) && is_array($translations)) {
            TranslationService::sync($record, 'translations', $translations);
        }

        return $record->fresh();
    }

    public static function updateByRequest(Faq $record, array $data): bool
    {
        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $updated = $record->update($data);

        if (! empty($translations) && is_array($translations)) {
            TranslationService::sync($record, 'translations', $translations);
        }

        return $updated;
    }

    public static function delete(Faq $record): bool
    {
        return (bool) $record->delete();
    }
}
