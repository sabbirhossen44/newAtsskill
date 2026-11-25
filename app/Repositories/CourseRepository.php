<?php

namespace App\Repositories;

use App\Services\TranslationService;

use Illuminate\Support\Facades\Schema;

use App\Enums\CourseApprovalEnum;
use App\Enums\CourseStatusEnum;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseChapterLesson;
use App\Models\CourseDeleteRequest;
use App\Models\CourseLanguage;
use App\Models\CourseLevel;
use App\Models\CourseMedia;
use App\Models\CoursePartnerInstructor;
use App\Models\CourseSelectedLanguage;
use App\Models\CourseSelectedLevel;
use App\Models\Media;
use App\Models\Quiz;
use App\Models\User;
use Arafat\LaravelRepository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CourseRepository extends Repository
{
    public static function model()
    {
        return Course::class;
    }

    public static function findById(int $id): ?Course
    {
        return static::model()::query()->find($id);
    }

    public static function findByIdOrFail(int $id): Course
    {
        return static::model()::query()->findOrFail($id);
    }

    public static function findBySlug(string $slug): ?Course
    {
        return static::model()::query()->where('slug', $slug)->first();
    }

    /**
     * Public course-listing filter options (cached 24h, as the old
     * CoursePageController did).
     */
    public static function filterOptions(): array
    {
        return [
            'categories' => CourseCategoryRepository::activeRootCategories(),
            'languages' => Cache::remember('course_page_languages', 1440, function () {
                return CourseLanguage::where('status', 1)->get();
            }),
            'levels' => Cache::remember('course_page_levels', 1440, function () {
                return CourseLevel::where('status', 1)->with('translation')->get();
            }),
        ];
    }

    /**
     * Filtered, paginated public course list for fetchCourses().
     * Query chain moved verbatim from the old controller, including
     * the ungrouped orWhere() in the free-price branch.
     */
    public static function paginateFiltered(Request $request, int $perPage = 12): LengthAwarePaginator
    {
        $query = static::model()::query();
        $query->where(['is_approved' => CourseApprovalEnum::APPROVED->value, 'status' => CourseStatusEnum::ACTIVE->value]);
        $query->whereHas('category', function ($q) use ($request) {
            $q->where('status', 1);
        });

        $query->when($request->search, function ($q) use ($request) {
            $q->where('title', 'like', '%'.$request->search.'%');
        });

        $query->when($request->main_category, function ($q) use ($request) {
            $q->whereHas('category', function ($q) use ($request) {
                $q->whereHas('parentCategory', function ($q) use ($request) {
                    $q->where('slug', $request->main_category);
                });
            });
        });

        $query->when($request->category && $request->filled('category'), function ($q) use ($request) {
            $categoriesIds = is_array($request->category) ? $request->category : explode(',', $request->category);
            $q->whereIn('category_id', $categoriesIds);
        });

        $query->when($request->language && $request->filled('language'), function ($q) use ($request) {
            $languagesIds = is_array($request->language) ? $request->language : explode(',', $request->language);
            $q->whereHas('languages', function ($q) use ($languagesIds) {
                $q->whereIn('language_id', $languagesIds);
            });
        });

        $query->when($request->price, function ($q) use ($request) {
            if ($request->price == 'paid') {
                $q->where('price', '>', 0);
            } else {
                $q->where('price', 0)->orWhere('price', null);
            }
        });

        $query->when($request->level, function ($q) use ($request) {
            $levelsIds = is_array($request->level) ? $request->level : explode(',', $request->level);
            $q->whereHas('levels', function ($q) use ($levelsIds) {
                $q->whereIn('level_id', $levelsIds);
            });
        });

        $query->with(['instructor:id,name', 'enrollments', 'category.translation']);
        $query->orderBy('created_at', $request->order && $request->filled('order') ? $request->order : 'desc');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Course details with chapters/items/lessons/quizzes and the
     * approved-review count (cached 24h per slug, as before).
     */
    public static function findBySlugWithDetailsOrFail(string $slug): Course
    {
        return Cache::remember('course_details_'.$slug, 1440, function () use ($slug) {
            return static::model()::active()->with(['chapters' => function ($query) {
                $query->orderBy('order', 'asc')->with(['chapterItems', 'chapterItems.lesson', 'chapterItems.quiz']);
            }])
                ->withCount(['reviews' => function ($query) {
                    $query->where('status', 1)->whereHas('course')->whereHas('user');
                }])
                ->where('slug', $slug)->firstOrFail();
        });
    }

    public static function lessonCount(Course $course): int
    {
        return Cache::remember('course_lesson_count_'.$course->slug, 1440, function () use ($course) {
            return CourseChapterLesson::where('course_id', $course->id)->count();
        });
    }

    public static function quizCount(Course $course): int
    {
        return Cache::remember('course_quiz_count_'.$course->slug, 1440, function () use ($course) {
            return Quiz::where('course_id', $course->id)->count();
        });
    }

    public static function findWithTrashedById(int $id): ?Course
    {
        return static::model()::query()->withTrashed()->find($id);
    }

    public static function activeCourses(): Collection
    {
        return static::model()::query()->active()->get();
    }

    public static function findActiveById(string $id): ?Course
    {
        return static::model()::query()->active()->where('id', $id)->first();
    }

    public static function findActiveBySlugOrFail(string $slug): Course
    {
        return static::model()::query()->active()->where('slug', $slug)->firstOrFail();
    }

    public static function findActiveBySlugWithContent(string $slug): ?Course
    {
        return static::model()::query()->active()->with([
            'chapters',
            'chapters.chapterItems',
            'chapters.chapterItems.lesson',
            'chapters.chapterItems.quiz',
        ])->withTrashed()->where('slug', $slug)->first();
    }

    public static function isOwnCourse(string $id, ?int $userId): bool
    {
        return static::model()::query()->where('id', $id)->where('instructor_id', $userId)->exists();
    }

    public static function instructorCourseIds(int $instructorId): array
    {
        return static::model()::query()->where('instructor_id', $instructorId)->pluck('id')->toArray();
    }

    public static function countForInstructor(int $instructorId): int
    {
        return static::model()::query()->where('instructor_id', $instructorId)->count();
    }

    public static function countPendingForInstructor(int $instructorId): int
    {
        return static::model()::query()->where('instructor_id', $instructorId)
            ->where(['status' => 'pending', 'is_approved' => 'pending'])->count();
    }

    public static function paginateForInstructor(int $instructorId, int $perPage = 10)
    {
        return static::model()::query()->where('instructor_id', $instructorId)->paginate($perPage);
    }

    /**
     * Instructor course wizard, step 1: create a new course or update the
     * basic fields of the existing one. $data is the validated step-1 data
     * plus slug / edit_mode / id from the request.
     */
    public static function storeWizard(array $data, int $instructorId): Course
    {
        if (($data['edit_mode'] ?? 0) == 1) {
            $course = static::model()::query()->findOrFail($data['id']);
        } else {
            $course = new Course();
        }

        $course->title = $data['title'];
        $course->slug = $data['slug'];
        $course->instructor_id = $instructorId;
        $course->seo_description = $data['seo_description'] ?? null;
        $course->thumbnail = $data['thumbnail'];
        $course->demo_video_storage = $data['demo_video_storage'] ?? null;
        $course->demo_video_source = ($data['demo_video_storage'] ?? null) == 'upload'
            ? ($data['upload_path'] ?? null)
            : ($data['external_path'] ?? null);
        $course->price = $data['price'];
        $course->discount = $data['discount_price'] ?? null;
        $course->description = $data['description'];
        $course->save();

        return $course;
    }

    /** Wizard step 2 data: categories, levels, languages and the course's category. */
    public static function moreInfoData(int $courseId): array
    {
        $course = static::model()::query()->findOrFail($courseId);

        return [
            'courseId' => $courseId,
            'course' => $course,
            'categories' => CourseCategory::where('status', 1)->get(),
            'levels' => CourseLevel::with(['translation'])->where('status', 1)->get(),
            'category' => CourseCategory::find($course->category_id),
            'languages' => CourseLanguage::where('status', 1)->get(),
        ];
    }

    /** Wizard step 2 save: course flags + partner instructors / levels / languages. */
    public static function updateMoreInfo(Course $course, array $data): void
    {
        $course->capacity = $data['capacity'] ?? null;
        $course->duration = $data['course_duration'];
        $course->category_id = $data['category'];
        $course->qna = $data['qna'] ?? null;
        $course->downloadable = $data['downloadable'] ?? null;
        $course->certificate = $data['certificate'] ?? null;
        $course->partner_instructor = $data['partner_instructor'] ?? null;
        $course->save();

        // delete unselected partner instructor
        CoursePartnerInstructor::where('course_id', $course->id)
            ->whereNotIn('instructor_id', $data['partner_instructors'] ?? [])->delete();

        // insert partner instructor
        foreach ($data['partner_instructors'] ?? [] as $instructor) {
            CoursePartnerInstructor::updateOrCreate(
                ['course_id' => $course->id, 'instructor_id' => $instructor],
            );
        }

        // insert levels
        CourseSelectedLevel::where('course_id', $course->id)
            ->whereNotIn('level_id', $data['levels'] ?? [])->delete();

        foreach ($data['levels'] ?? [] as $level) {
            CourseSelectedLevel::updateOrCreate(
                ['course_id' => $course->id, 'level_id' => $level],
            );
        }

        //insert languages
        CourseSelectedLanguage::where('course_id', $course->id)
            ->whereNotIn('language_id', $data['languages'] ?? [])->delete();

        foreach ($data['languages'] ?? [] as $language) {
            CourseSelectedLanguage::updateOrCreate(
                ['course_id' => $course->id, 'language_id' => $language],
            );
        }
    }

    /** Wizard step 4 save: reviewer message + status. */
    public static function updateFinish(Course $course, array $data): void
    {
        $course->message_for_reviewer = $data['message_for_reviewer'] ?? null;
        $course->status = $data['status'];
        $course->save();
    }

    /** Partner-instructor search for the wizard (excludes the current user). */
    public static function instructorsForPartner(string $query, int $excludeId)
    {
        return User::where('role', 'instructor')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%'.$query.'%')
                    ->orWhere('email', 'like', '%'.$query.'%');
            })
            ->where('id', '!=', $excludeId)
            ->get();
    }

    /** Category filter options for the wizard (levels / category / languages). */
    public static function filtersForCategory(string $id): array
    {
        return [
            'levels' => CourseLevel::with(['translation'])->where('status', 1)->get(),
            'category' => CourseCategory::find($id),
            'languages' => CourseLanguage::where('status', 1)->get(),
        ];
    }

    /**
     * Course delete request. Returns false when a request already exists
     * for the course, true when a new one was created.
     */
    public static function createDeleteRequest(Course $course, string $message): bool
    {
        if (CourseDeleteRequest::where('course_id', $course->id)->exists()) {
            return false;
        }

        $deleteRequest = new CourseDeleteRequest();
        $deleteRequest->course_id = $course->id;
        $deleteRequest->message = $message;
        $deleteRequest->save();

        return true;
    }

    public static function storeByRequest(array $data): Course
    {
        $data['slug'] = Course::generateUniqueSlug($data['title'] ?? Str::random(8));

        $relations = $data;
        unset($data['level_ids'], $data['language_ids'], $data['medias']);

        $course = self::create($data);

        $data = $relations;

        static::syncRelations($course, $data);

        return $course->fresh();
    }

    public static function updateByRequest(Course $course, array $data): bool
    {
        if (! empty($data['title'])) {
            $data['slug'] = Course::generateUniqueSlug($data['title'], $course->id);
        }

        $relations = $data;
        unset($data['level_ids'], $data['language_ids'], $data['medias']);

        $updated = self::update($course, $data);

        static::syncRelations($course, $relations);

        return $updated;
    }

    protected static function syncRelations(Course $course, array $data): void
    {
        if (array_key_exists('level_ids', $data)) {
            $course->levels()->delete();
            foreach ((array) $data['level_ids'] as $levelId) {
                $course->levels()->create(['level_id' => $levelId]);
            }
        }

        if (array_key_exists('language_ids', $data)) {
            $course->languages()->delete();
            foreach ((array) $data['language_ids'] as $languageId) {
                $course->languages()->create(['language_id' => $languageId]);
            }
        }

        if (! empty($data['medias'])) {
            static::syncForParent($course, $data['medias'], CourseMedia::class, 'course_id');
        }
    }

    /**
     * Sync a course's media pivots. Moved here from MediaRepository:
     * Sabbir's pasted verbatim MediaRepository does not ship
     * syncForParent(), so the pivot logic lives with its only caller.
     *
     * $medias is a list of new uploads and/or kept media, each item one of:
     * an UploadedFile, a Media model, a media id, or an array with a
     * 'file' (UploadedFile) and/or 'media_id'/'id' plus optional
     * 'is_primary' / 'order'. It may also be shaped as
     * ['files' => [...], 'existing_media_ids' => [...]]. New files are
     * stored via MediaRepository::storeByRequest($file, 'courses', 'image').
     * Pivot rows are created with is_primary/order (the first kept item
     * is primary when none is flagged), and pivots whose media_id is not
     * in $existingMediaIds (plus the ids stored/kept in this call) are
     * deleted. The Media records themselves are never deleted here.
     */
    public static function syncForParent(Course $course, array $medias, string $pivotClass = CourseMedia::class, string $foreignKey = 'course_id', array $existingMediaIds = []): void
    {
        $items = $medias['files'] ?? $medias['items'] ?? $medias;
        $existingMediaIds = array_merge(
            $existingMediaIds,
            array_map('intval', (array) ($medias['existing_media_ids'] ?? $medias['existing_ids'] ?? []))
        );

        $entries = [];
        $order = 0;

        foreach ((array) $items as $item) {
            if ($item instanceof UploadedFile) {
                $file = $item;
                $isPrimary = null;
            } elseif ($item instanceof Media) {
                $entries[] = ['media_id' => $item->id, 'is_primary' => null, 'order' => $order++];
                continue;
            } elseif (is_numeric($item)) {
                $entries[] = ['media_id' => (int) $item, 'is_primary' => null, 'order' => $order++];
                continue;
            } elseif (is_array($item) && isset($item['file']) && $item['file'] instanceof UploadedFile) {
                $file = $item['file'];
                $isPrimary = $item['is_primary'] ?? null;
            } elseif (is_array($item) && isset($item['media_id'])) {
                $entries[] = ['media_id' => (int) $item['media_id'], 'is_primary' => $item['is_primary'] ?? null, 'order' => $item['order'] ?? $order++];
                continue;
            } elseif (is_array($item) && isset($item['id'])) {
                $entries[] = ['media_id' => (int) $item['id'], 'is_primary' => $item['is_primary'] ?? null, 'order' => $item['order'] ?? $order++];
                continue;
            } else {
                continue;
            }

            $media = MediaRepository::storeByRequest($file, 'courses', 'image');
            $entries[] = ['media_id' => $media->id, 'is_primary' => $isPrimary, 'order' => is_array($item) ? ($item['order'] ?? $order++) : $order++];
        }

        $hasPrimary = collect($entries)->contains(fn ($entry) => ! empty($entry['is_primary']));

        foreach ($entries as $index => $entry) {
            $pivotClass::query()->updateOrCreate(
                [$foreignKey => $course->id, 'media_id' => $entry['media_id']],
                ['is_primary' => $hasPrimary ? (bool) $entry['is_primary'] : $index === 0, 'order' => $entry['order']]
            );
        }

        // Kept pivots not re-stated in this call lose primary status, so
        // exactly one pivot is primary (the flagged entry, else the first).
        if ($entries !== []) {
            $pivotClass::query()->where($foreignKey, $course->id)
                ->whereNotIn('media_id', collect($entries)->pluck('media_id')->all())
                ->update(['is_primary' => false]);
        }

        $keptMediaIds = array_unique(array_merge(
            $existingMediaIds,
            collect($entries)->pluck('media_id')->all()
        ));

        $pivotClass::query()
            ->where($foreignKey, $course->id)
            ->whereNotIn('media_id', $keptMediaIds)
            ->delete();
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


    protected static function adminAttributes(array $data): array
    {
        $bool = fn ($key) => isset($data[$key]) && in_array($data[$key], [1, '1', true, 'on', 'active'], true) ? 1 : 0;

        return [
            'title' => $data['title'],
            'slug' => $data['slug'] ?? \Illuminate\Support\Str::slug($data['title']),
            'category_id' => $data['category_id'] ?? null,
            'instructor_id' => $data['instructor_id'] ?? null,
            'price' => $data['price'] ?? 0,
            'discount' => $data['discount'] ?? null,
            'description' => $data['description'] ?? null,
            'thumbnail' => $data['thumbnail'] ?? null,
            'type' => $data['type'] ?? null,
            'status' => (\App\Enums\CourseStatusEnum::tryFrom((string) ($data['status'] ?? '')) ?? \App\Enums\CourseStatusEnum::INACTIVE)->value,
            'certificate' => $bool('certificate'),
            'downloadable' => $bool('downloadable'),
            'qna' => $bool('qna'),
            'duration' => $data['duration'] ?? null,
            'start_date' => $data['start_date'] ?? null,
        ];
    }

    public static function storeAdmin(array $data): Course
    {
        $attrs = static::adminAttributes($data);
        $attrs['is_approved'] = \App\Enums\CourseApprovalEnum::PENDING->value;

        return static::model()::query()->create($attrs);
    }

    public static function updateAdmin(Course $course, array $data): bool
    {
        return $course->update(static::adminAttributes($data));
    }

}
