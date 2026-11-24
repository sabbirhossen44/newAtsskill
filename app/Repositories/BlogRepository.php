<?php

namespace App\Repositories;

use App\Models\Blog;
use App\Services\TranslationService;

class BlogRepository
{
    public static function model()
    {
        return Blog::class;
    }

    public static function findById(int $id): ?Blog
    {
        return static::model()::query()->find($id);
    }

    public static function findBySlug(string $slug): ?Blog
    {
        return static::model()::query()->where('slug', $slug)->first();
    }

    public static function storeByRequest(array $data): Blog
    {
        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $data['slug'] = Blog::generateUniqueSlug($translations['en']['title'] ?? $data['slug'] ?? \Illuminate\Support\Str::random(8));
        $record = static::model()::query()->create($data);

        if (! empty($translations) && is_array($translations)) {
            TranslationService::sync($record, 'translations', $translations);
        }

        return $record->fresh();
    }

    public static function updateByRequest(Blog $record, array $data): bool
    {
        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $updated = $record->update($data);

        if (! empty($translations) && is_array($translations)) {
            TranslationService::sync($record, 'translations', $translations);
        }

        return $updated;
    }

    public static function delete(Blog $record): bool
    {
        return (bool) $record->delete();
    }
}
