<?php

namespace App\Repositories;

use Arafat\LaravelRepository\Repository;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\CourseChapterItem;
use App\Models\CourseChapterLesson;
use App\Models\CourseProgress;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizResult;
use Illuminate\Database\Eloquent\Collection;

class LearningRepository extends Repository
{
    public static function model()
    {
        return CourseProgress::class;
    }

    public static function currentProgress(int $courseId, int $userId): ?CourseProgress
    {
        return CourseProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('current', 1)
            ->orderBy('id', 'desc')
            ->first();
    }

    public static function watchedLessonIds(int $courseId, int $userId, string $type): array
    {
        return CourseProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('type', $type)
            ->where('watched', 1)
            ->pluck('lesson_id')
            ->toArray();
    }

    public static function announcementsForCourse(int $courseId): Collection
    {
        return Announcement::where('course_id', $courseId)->orderBy('id', 'desc')->get();
    }

    public static function lectureCount(int $courseId): int
    {
        return CourseChapterItem::whereHas('chapter', function ($q) use ($courseId) {
            $q->where('course_id', $courseId);
        })->count();
    }

    public static function completedCount(int $courseId, int $userId): int
    {
        return CourseProgress::where('user_id', $userId)
            ->where('course_id', $courseId)->where('watched', 1)->count();
    }

    /** Create the initial "current" progress row on the first chapter lesson. */
    public static function ensureInitialProgress(Course $course, int $userId): ?CourseProgress
    {
        $lessonId = @$course->chapters?->first()?->chapterItems()?->first()?->lesson->id;

        if ($lessonId) {
            return CourseProgress::create([
                'user_id' => $userId,
                'course_id' => $course->id,
                'chapter_id' => $course->chapters->first()->id,
                'lesson_id' => $lessonId,
                'current' => 1,
            ]);
        }

        return null;
    }

    /** Mark all progress rows for the course as not current, then upsert the current one. */
    public static function setCurrentProgress(array $input, int $userId): CourseProgress
    {
        CourseProgress::where('course_id', $input['courseId'])->update(['current' => 0]);

        return CourseProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'course_id' => $input['courseId'],
                'chapter_id' => $input['chapterId'],
                'lesson_id' => $input['lessonId'],
                'type' => $input['type'],
            ],
            [
                'current' => 1,
            ]
        );
    }

    public static function lessonFileInfo(string $lessonId): CourseChapterLesson
    {
        return CourseChapterLesson::select(['id', 'file_path', 'storage', 'file_type', 'downloadable', 'description'])->findOrFail($lessonId);
    }

    public static function liveFileInfo(string $lessonId): CourseChapterLesson
    {
        return CourseChapterLesson::with([
            'course:id,instructor_id,slug',
            'course.instructor:id',
            'course.instructor.zoom_credential:id,instructor_id,client_id,client_secret',
            'course.instructor.jitsi_credential:id,instructor_id,app_id,api_key,permissions',
            'live:id,lesson_id,start_time,type,meeting_id,password,join_url',
        ])->select([
            'id', 'course_id', 'chapter_item_id', 'title', 'description',
            'duration', 'file_path', 'storage', 'file_type', 'downloadable',
        ])->findOrFail($lessonId);
    }

    public static function progressForLesson(string $lessonId, int $userId, string $type): ?CourseProgress
    {
        return CourseProgress::where(['lesson_id' => $lessonId, 'user_id' => $userId, 'type' => $type])->first();
    }

    public static function findLessonOrFail(string $lessonId): CourseChapterLesson
    {
        return CourseChapterLesson::findOrFail($lessonId);
    }

    public static function findQuizOrFail(string $id): Quiz
    {
        return Quiz::withCount('questions')->findOrFail($id);
    }

    public static function findQuizResultOrFail(string $id): QuizResult
    {
        return QuizResult::findOrFail($id);
    }

    /** Grade a quiz submission (correct answers add the question grade) and store the result. */
    public static function gradeQuiz(Quiz $quiz, array $answers, int $userId): QuizResult
    {
        $grad = 0;
        $result = [];
        foreach ($answers as $key => $questionAns) {
            $question = QuizQuestion::findOrFail($key);
            $answer = $question->answers->where('correct', 1)->pluck('id')->toArray();

            if (in_array($questionAns, $answer)) {
                $grad += $question->grade;
            }
            $result[$key] = [
                'answer' => $questionAns,
                'correct' => in_array($questionAns, $answer),
            ];
        }

        return QuizResultRepository::storeByRequest([
            'user_id' => $userId,
            'quiz_id' => $quiz->id,
            'result' => json_encode($result),
            'user_grade' => $grad,
            'status' => $grad >= $quiz->pass_mark ? 'pass' : 'failed',
        ]);
    }

    public static function liveLesson(string $lessonId): CourseChapterLesson
    {
        return CourseChapterLesson::select('id', 'course_id', 'chapter_item_id', 'title')->with(['course' => function ($q) {
            $q->select('id', 'instructor_id', 'slug');
        }, 'course.instructor' => function ($q) {
            $q->select('id');
        }, 'course.instructor.zoom_credential' => function ($q) {
            $q->select('id', 'instructor_id', 'client_id', 'client_secret');
        }, 'chapterItem' => function ($q) {
            $q->select('id', 'type');
        }, 'live' => function ($q) {
            $q->select('id', 'lesson_id', 'start_time', 'type', 'meeting_id', 'password', 'join_url');
        }])->findOrFail($lessonId);
    }
}
