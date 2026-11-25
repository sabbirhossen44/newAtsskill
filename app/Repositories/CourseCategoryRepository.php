<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Schema;

use Illuminate\Pagination\LengthAwarePaginator;

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

    public static function delete(mixed $record): bool
    {
        return (bool) $record->delete();
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


    public static function paginateSubCategories(int $parentId, int $perPage = 15): LengthAwarePaginator
    {
        return static::model()::query()->where('parent_id', $parentId)->latest()->paginate($perPage);
    }

    public static function storeSubCategory(array $data, int $parentId): CourseCategory
    {
        $data['parent_id'] = $parentId;
        [$direct, $translations] = static::splitAdminData($data);
        $direct['slug'] = CourseCategory::generateUniqueSlug(
            $translations['en']['name'] ?? $data['slug'] ?? \Illuminate\Support\Str::random(8)
        );

        $record = static::model()::query()->create($direct);
        static::syncAdminTranslations($record, $translations);

        return $record->fresh();
    }

    public static function updateSubCategory(CourseCategory $record, array $data): bool
    {
        return static::updateByRequest($record, $data);
    }

}
