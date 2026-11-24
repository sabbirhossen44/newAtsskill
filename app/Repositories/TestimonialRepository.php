<?php

namespace App\Repositories;

use App\Models\Testimonial;
use App\Services\TranslationService;

class TestimonialRepository
{
    public static function model()
    {
        return Testimonial::class;
    }

    public static function findById(int $id): ?Testimonial
    {
        return static::model()::query()->find($id);
    }

    public static function storeByRequest(array $data): Testimonial
    {
        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $record = static::model()::query()->create($data);

        if (! empty($translations) && is_array($translations)) {
            TranslationService::sync($record, 'translations', $translations);
        }

        return $record->fresh();
    }

    public static function updateByRequest(Testimonial $record, array $data): bool
    {
        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $updated = $record->update($data);

        if (! empty($translations) && is_array($translations)) {
            TranslationService::sync($record, 'translations', $translations);
        }

        return $updated;
    }

    public static function delete(Testimonial $record): bool
    {
        return (bool) $record->delete();
    }
}
