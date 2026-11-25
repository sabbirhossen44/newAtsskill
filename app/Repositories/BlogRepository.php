<?php

namespace App\Repositories;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Services\TranslationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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

    /**
     * Public blog listing (cached 1h; cache key includes the search,
     * category and page, exactly as the old BlogController built it).
     */
    public static function paginateForIndex(?string $search, ?string $categorySlug, int $page = 1): LengthAwarePaginator
    {
        $cacheDuration = 3600;
        $cacheKey = 'blog_index_'.md5($search.'_'.$categorySlug.'_page_'.$page);

        return Cache::remember($cacheKey, $cacheDuration, function () use ($search, $categorySlug) {
            $query = static::model()::query();

            $query->when($search, function ($query) use ($search) {
                $query->whereHas('translation', function ($query) use ($search) {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            });

            $query->when($categorySlug, function ($query) use ($categorySlug) {
                $query->whereHas('category', function ($query) use ($categorySlug) {
                    $query->where('slug', $categorySlug);
                });
            });

            $query->whereHas('category', function ($q) { $q->where('status', 1); });

            return $query->where(['status' => 1])->orderBy('created_at', 'desc')->paginate(9);
        });
    }

    public static function activeCategories(): Collection
    {
        return Cache::remember('blog_categories_all', 3600, function () {
            return BlogCategory::where('status', 1)->get();
        });
    }

    public static function popular(): Collection
    {
        return Cache::remember('blog_popular_list', 3600, function () {
            return static::model()::query()->where(['status' => 1])
                ->whereHas('category', function ($q) { $q->where('status', 1); })
                ->where('is_popular', 1)
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();
        });
    }

    public static function findBySlugWithActiveCategoryOrFail(string $slug): Blog
    {
        return Cache::remember("blog_detail_{$slug}", 3600, function () use ($slug) {
            return static::model()::query()->where('slug', $slug)
                ->whereHas('category', function ($q) { $q->where('status', 1); })
                ->firstOrFail();
        });
    }

    public static function latestExcept(Blog $blog, int $limit = 8): Collection
    {
        return Cache::remember("blog_latest_{$blog->id}", 3600, function () use ($blog, $limit) {
            return static::model()::query()->where(['status' => 1])
                ->where('id', '!=', $blog->id)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }
}
