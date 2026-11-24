<?php

namespace App\Repositories;

use App\Models\CourseCategory;
use App\Services\TranslationService;
use Illuminate\Support\Str;

class CourseCategoryRepository
{
    public static function model()
    {
        return CourseCategory::class;
    }

    public static function findById(int $id): ?CourseCategory
    {
        return static::model()::query()->find($id);
    }

    public static function findBySlug(string $slug): ?CourseCategory
    {
        return static::model()::query()->where('slug', $slug)->first();
    }

    public static function storeByRequest(array $data): CourseCategory
    {
        $data['slug'] = CourseCategory::generateUniqueSlug(
            $data['translations']['en']['name'] ?? $data['slug'] ?? Str::random(8)
        );

        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $category = static::model()::query()->create($data);

        if (! empty($translations)) {
            TranslationService::sync($category, 'translations', $translations);
        }

        return $category->fresh();
    }

    public static function updateByRequest(CourseCategory $category, array $data): bool
    {
        if (! empty($data['translations']['en']['name'])) {
            $data['slug'] = CourseCategory::generateUniqueSlug($data['translations']['en']['name'], $category->id);
        }

        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        $updated = $category->update($data);

        if (! empty($translations)) {
            TranslationService::sync($category, 'translations', $translations);
        }

        return $updated;
    }
}
