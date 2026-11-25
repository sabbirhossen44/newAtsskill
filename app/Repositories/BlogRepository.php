<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Schema;

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

    public static function paginateAdmin(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = static::model()::query()->latest();

        if ($search !== null && $search !== '') {
            $table = (new (static::model()))->getTable();
            $query->where(function ($q) use ($search, $table) {
                foreach (['name', 'title', 'email', 'code', 'slug', 'coupon_code'] as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $q->orWhere($column, 'like', '%'.$search.'%');
                    }
                }
            });
        }

        return $query->paginate($perPage);
    }

    public static function findByIdOrFail(int $id): mixed
    {
        return static::model()::query()->findOrFail($id);
    }

    public static function toggleStatus(mixed $record): bool
    {
        $raw = $record->getAttributes()['status'] ?? null;

        if (is_string($raw) && in_array($raw, ['active', 'inactive'], true)) {
            $new = $raw === 'active' ? 'inactive' : 'active';
        } else {
            $new = $raw ? 0 : 1;
        }

        return (bool) $record->update(['status' => $new]);
    }

    public static function updateStatus(mixed $record, mixed $status): bool
    {
        return (bool) $record->update(['status' => static::normalizeStatusValue($record, $status)]);
    }

    protected static function normalizeStatusValue(mixed $record, mixed $status): mixed
    {
        if (! in_array($status, ['active', 'inactive'], true)) {
            return $status;
        }

        $casts = $record->getCasts();
        $cast = $casts['status'] ?? null;

        if (in_array($cast, ['boolean', 'bool', 'int', 'integer'], true)) {
            return $status === 'active' ? 1 : 0;
        }

        if (is_string($cast) && enum_exists($cast)) {
            return $status;
        }

        try {
            $type = Schema::getColumnType($record->getTable(), 'status');
            if (in_array($type, ['boolean', 'tinyint', 'smallint', 'integer', 'bigint'], true)) {
                return $status === 'active' ? 1 : 0;
            }
        } catch (\Throwable) {
        }

        return $status;
    }

    /**
     * Split validated admin data into direct table columns and translation
     * fields. Form fields that are neither (e.g. a display-only `code`)
     * are dropped unless the model has a `translations` relation, in
     * which case they are synced for the request language (default `en`).
     * Status strings ('active'/'inactive') are normalised to the column
     * type (boolean columns get 1/0; enum-cast columns keep the string).
     */
    protected static function splitAdminData(array $data): array
    {
        $model = new (static::model());
        $columns = Schema::getColumnListing($model->getTable());
        $data = array_diff_key($data, array_flip(['_token', '_method']));
        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        if (array_key_exists('status', $data) && in_array('status', $columns, true)) {
            $data['status'] = static::normalizeStatusValue($model, $data['status']);
        }

        $direct = array_intersect_key($data, array_flip($columns));
        $rest = array_diff_key($data, array_flip($columns));

        if ($translations === null && $rest !== [] && method_exists($model, 'translations')) {
            $lang = 'en';
            if (is_string($data['code'] ?? null) && ! in_array('code', $columns, true)) {
                $lang = $data['code'];
            }
            $translations = [$lang => $rest];
        }

        return [$direct, is_array($translations) ? $translations : []];
    }

    protected static function syncAdminTranslations(mixed $record, array $translations): void
    {
        if ($translations !== [] && method_exists($record, 'translations')) {
            TranslationService::sync($record, 'translations', $translations);
        }
    }

}
