<?php

namespace App\Repositories;

use App\Enums\CourseStatusEnum;
use App\Models\CourseCategory;
use App\Services\TranslationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
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

    /**
     * Trending homepage categories (cached 1h, as the old
     * HomePageController did).
     */
    public static function trending(): Collection
    {
        return Cache::remember('home_trending_categories', 3600, function () {
            return static::model()::query()->with(['translation:id,name,course_category_id', 'subCategories' => function ($query) {
                $query->withCount(['courses' => function ($query) {
                    $query->where('status', CourseStatusEnum::ACTIVE->value);
                }]);
            }])->withCount(['subCategories as active_sub_categories_count' => function ($query) {
                $query->whereHas('courses', function ($query) {
                    $query->where('status', CourseStatusEnum::ACTIVE->value);
                });
            }])->whereNull('parent_id')
                ->where('status', 1)
                ->where('show_at_trending', 1)
                ->get();
        });
    }

    /**
     * Root categories for the public course-listing filters
     * (cached 24h, as the old CoursePageController did).
     */
    public static function activeRootCategories(): Collection
    {
        return Cache::remember('course_page_categories', 1440, function () {
            return static::model()::active()->whereNull('parent_id')->with(['translation'])->get();
        });
    }

    public static function subCategoriesForMainCategory(string $slug): Collection
    {
        return static::model()::query()->whereHas('parentCategory', function ($q) use ($slug) {
            $q->where('slug', $slug);
        })->with('translation')->get();
    }
}
