<?php

namespace App\Repositories;

use App\Services\TranslationService;

use Illuminate\Support\Facades\Schema;


use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CourseReviewRepository
{
    public static function model()
    {
        return CourseReview::class;
    }

    public static function countForUser(int $userId): int
    {
        return static::model()::query()->where('user_id', $userId)->count();
    }

    public static function paginateForUser(int $userId, int $perPage = 30): LengthAwarePaginator
    {
        return static::model()::query()
            ->with('course:title,id')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public static function findForUserOrFail(int $id, int $userId): CourseReview
    {
        return static::model()::query()
            ->with('course:id,title')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public static function deleteForUser(int $id, int $userId): bool
    {
        return (bool) static::model()::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();
    }

    public static function storeByRequest(array $data): CourseReview
    {
        return static::model()::query()->create($data);
    }

    public static function findForUser(int $courseId, int $userId): ?CourseReview
    {
        return static::model()::query()->where(['course_id' => $courseId, 'user_id' => $userId])->first();
    }

    public static function paginateForCourse(int $courseId, int $page = 1, int $perPage = 8): LengthAwarePaginator
    {
        return static::model()::query()->where(['course_id' => $courseId, 'status' => 1])
            ->whereHas('course')->whereHas('user')->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Course details page listing: 20 per page, newest first.
     * Kept separate from paginateForCourse() (learning-player listing,
     * 8 per page ordered by id) so neither caller changes behaviour.
     */
    public static function paginateForCoursePage(Course $course, int $perPage = 20): LengthAwarePaginator
    {
        return static::model()::query()
            ->where('course_id', $course->id)
            ->where('status', 1)
            ->whereHas('course')
            ->whereHas('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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

    public static function updateByRequest(mixed $record, array $data): bool
    {
        [$direct, $translations] = static::splitAdminData($data);

        $updated = $record->update($direct);
        static::syncAdminTranslations($record, $translations);

        return (bool) $updated;
    }

}
