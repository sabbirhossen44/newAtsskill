<?php

namespace App\Repositories;

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
}
