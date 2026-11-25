<?php

namespace App\Repositories;

use App\Models\Announcement;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

class AnnouncementRepository
{
    public static function model()
    {
        return Announcement::class;
    }

    public static function findById(int $id): ?Announcement
    {
        return static::model()::query()->find($id);
    }

    public static function findByIdOrFail(string $id): Announcement
    {
        return static::model()::query()->findOrFail($id);
    }

    public static function allForInstructor(int $instructorId): Collection
    {
        return static::model()::query()->with(['course:id,title'])
            ->where('instructor_id', $instructorId)->orderBy('id', 'desc')->get();
    }

    public static function coursesForInstructor(int $instructorId): Collection
    {
        return Course::select(['id', 'title'])->where('instructor_id', $instructorId)->get();
    }

    public static function storeByRequest(array $data): Announcement
    {
        return static::model()::query()->create($data);
    }

    public static function updateByRequest(Announcement $announcement, array $data): bool
    {
        return $announcement->update($data);
    }

    public static function findForInstructorOrFail(string $id, int $instructorId): Announcement
    {
        return static::model()::query()->where('instructor_id', $instructorId)->where('id', $id)->firstOrFail();
    }

    public static function delete(Announcement $announcement): bool
    {
        return (bool) $announcement->delete();
    }
}
