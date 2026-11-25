<?php

namespace App\Repositories;

use App\Models\CourseChapter;
use App\Models\CourseChapterItem;
use App\Models\CourseChapterLesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Collection;

class CourseChapterRepository
{
    public static function model()
    {
        return CourseChapter::class;
    }

    public static function findById(string $id): ?CourseChapter
    {
        return static::model()::query()->find($id);
    }

    public static function findByIdOrFail(string $id): CourseChapter
    {
        return static::model()::query()->findOrFail($id);
    }

    public static function orderedForCourse(string $courseId): Collection
    {
        return static::model()::query()->where('course_id', $courseId)->orderBy('order', 'ASC')->get();
    }

    public static function orderedWithItemsForCourse(string $courseId): Collection
    {
        return static::model()::query()->with(['chapterItems'])
            ->where(['course_id' => $courseId, 'status' => 'active'])->orderBy('order')->get();
    }

    public static function storeByRequest(array $data): CourseChapter
    {
        $data['order'] = static::model()::query()->where('course_id', $data['course_id'])->max('order') + 1;

        return static::model()::query()->create($data);
    }

    public static function updateTitle(CourseChapter $chapter, ?string $title): bool
    {
        $chapter->title = $title;

        return $chapter->save();
    }

    public static function updateOrder(string $courseId, array $chapterIds): void
    {
        foreach ($chapterIds as $key => $value) {
            $chapter = static::model()::query()->where('course_id', $courseId)->find($value);
            $chapter->order = $key + 1;
            $chapter->save();
        }
    }

    /**
     * Delete a chapter with its items, lessons, quizzes, questions and
     * lesson files (same cascade as the old controller).
     */
    public static function destroyWithContent(CourseChapter $chapter): void
    {
        $chapterItems = CourseChapterItem::where('chapter_id', $chapter->id)
            ->where('instructor_id', auth('web')->user()->id)
            ->get();
        $lessonFiles = CourseChapterLesson::whereIn('chapter_item_id', $chapterItems->pluck('id'))->get();
        $quizIds = Quiz::whereIn('chapter_item_id', $chapterItems->pluck('id'))->pluck('id');
        $questionIds = QuizQuestion::whereIn('quiz_id', $quizIds)->pluck('id');

        // delete quizzes, questions, answers and lesson files
        QuizQuestion::whereIn('id', $questionIds)->delete();
        Quiz::whereIn('id', $quizIds)->delete();
        CourseChapterLesson::whereIn('id', $lessonFiles->pluck('id'))->delete();
        foreach ($lessonFiles as $lesson) {
            if (\File::exists(asset($lesson->file_path))) {
                \File::delete(asset($lesson->file_path));
            }
        }

        // delete chapter items and chapter
        CourseChapterItem::whereIn('id', $chapterItems->pluck('id'))->delete();
        $chapter->delete();
    }
}
