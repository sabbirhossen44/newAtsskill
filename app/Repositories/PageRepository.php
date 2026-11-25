<?php

namespace App\Repositories;

use App\Models\CustomPage;
use App\Services\TranslationService;
use Illuminate\Support\Str;

class PageRepository
{
    public static function model()
    {
        return CustomPage::class;
    }

    public static function findById(int $id): ?CustomPage
    {
        return static::model()::query()->find($id);
    }

    public static function findBySlug(string $slug): ?CustomPage
    {
        return static::model()::query()->where('slug', $slug)->first();
    }

    public static function findBySlugOrFail(string $slug): CustomPage
    {
        return static::model()::query()->where('slug', $slug)->firstOrFail();
    }

    public static function storeByRequest(array $data): CustomPage
    {
        $data['slug'] = CustomPage::generateUniqueSlug(
            $data['translations']['en']['name'] ?? $data['slug'] ?? Str::random(8)
        );

        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $page = static::model()::query()->create($data);

        if (! empty($translations)) {
            TranslationService::sync($page, 'translations', $translations);
        }

        return $page->fresh();
    }

    public static function updateByRequest(CustomPage $page, array $data): bool
    {
        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $updated = $page->update($data);

        if (! empty($translations)) {
            TranslationService::sync($page, 'translations', $translations);
        }

        return $updated;
    }
}
