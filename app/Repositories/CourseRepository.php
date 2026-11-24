<?php

namespace App\Repositories;

use App\Models\Course;
use App\Models\CourseMedia;
use Illuminate\Support\Str;

class CourseRepository
{
    public static function model()
    {
        return Course::class;
    }

    public static function findById(int $id): ?Course
    {
        return static::model()::query()->find($id);
    }

    public static function findBySlug(string $slug): ?Course
    {
        return static::model()::query()->where('slug', $slug)->first();
    }

    public static function storeByRequest(array $data): Course
    {
        $data['slug'] = Course::generateUniqueSlug($data['title'] ?? Str::random(8));

        $relations = $data;
        unset($data['level_ids'], $data['language_ids'], $data['medias']);

        $course = static::model()::query()->create($data);

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

        $updated = $course->update($data);

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
            MediaRepository::syncForParent($course, $data['medias'], CourseMedia::class, 'course_id');
        }
    }
}
