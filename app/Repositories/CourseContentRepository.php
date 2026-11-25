<?php

namespace App\Repositories;

use Arafat\LaravelRepository\Repository;

use App\Services\AppMailService;
use App\Models\Course;
use App\Models\CourseChapterItem;
use App\Models\CourseChapterLesson;
use App\Models\CourseLiveClass;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Chapter-item / lesson / live-class / quiz writes for the instructor
 * course-content flow (moved from CourseContentController). Live-class
 * student mails go through AppMailService, as before.
 */
class CourseContentRepository extends Repository
{
    public static function model()
    {
        return CourseChapterItem::class;
    }

    public static function chaptersForCourse(string $courseId): Collection
    {
        return \App\Models\CourseChapter::where('course_id', $courseId)->orderBy('order')->get();
    }

    public static function findChapterItemOrFail(string $id): CourseChapterItem
    {
        return CourseChapterItem::with(['lesson', 'quiz'])->findOrFail($id);
    }

    public static function findChapterItem(string $id): ?CourseChapterItem
    {
        return CourseChapterItem::with(['lesson', 'quiz'])->find($id);
    }

    /** Store a lesson / document / live class / quiz chapter item. */
    public static function storeLesson(array $data, int $instructorId): void
    {
        $chapterItem = CourseChapterItem::create([
            'instructor_id' => $instructorId,
            'chapter_id' => $data['chapter_id'] ?? null,
            'type' => $data['type'],
            'order' => CourseChapterItem::whereChapterId($data['chapter_id'] ?? null)->count() + 1,
        ]);

        if ($data['type'] == 'lesson') {
            CourseChapterLesson::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'instructor_id' => $instructorId,
                'course_id' => $data['course_id'] ?? null,
                'chapter_id' => $data['chapter_id'] ?? null,
                'chapter_item_id' => $chapterItem->id,
                'file_path' => $data['source'] == 'upload' ? ($data['upload_path'] ?? null) : ($data['link_path'] ?? null),
                'storage' => $data['source'],
                'file_type' => $data['file_type'],
                'volume' => $data['volume'] ?? null,
                'duration' => $data['duration'],
                'is_free' => $data['is_free'] ?? null,
            ]);
        } elseif ($data['type'] == 'document') {
            CourseChapterLesson::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'instructor_id' => $instructorId,
                'course_id' => $data['course_id'] ?? null,
                'chapter_id' => $data['chapter_id'] ?? null,
                'chapter_item_id' => $chapterItem->id,
                'file_path' => $data['upload_path'] ?? null,
                'file_type' => $data['file_type'],
            ]);
        } elseif ($data['type'] == 'live') {
            $chapter_lesson = CourseChapterLesson::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'instructor_id' => $instructorId,
                'course_id' => $data['course_id'] ?? null,
                'chapter_id' => $data['chapter_id'] ?? null,
                'chapter_item_id' => $chapterItem->id,
                'duration' => $data['duration'],
                'storage' => 'live',
                'file_type' => 'live',
            ]);
            $start_time = date('Y-m-d H:i:s', strtotime($data['start_time']));
            $join_url = ($data['live_type'] ?? null) === 'zoom' ? ($data['join_url'] ?? null) : null;
            CourseLiveClass::create([
                'lesson_id' => $chapter_lesson->id,
                'start_time' => $start_time,
                'meeting_id' => $data['meeting_id'],
                'password' => $data['password'] ?? null,
                'join_url' => $join_url,
                'type' => $data['live_type'],
            ]);
            if (($data['student_mail_sent'] ?? null) == 'on') {
                static::sendLiveClassMail($chapter_lesson, $start_time, $join_url);
            }
        } elseif ($data['type'] == 'quiz') {
            Quiz::create([
                'chapter_item_id' => $chapterItem->id,
                'instructor_id' => $instructorId,
                'chapter_id' => $data['chapter'] ?? null,
                'course_id' => $data['course_id'] ?? null,
                'title' => $data['title'],
                'time' => $data['time_limit'] ?? null,
                'attempt' => $data['attempts'] ?? null,
                'pass_mark' => $data['pass_mark'],
                'total_mark' => $data['total_mark'],
            ]);
        }
    }

    /** Update a lesson / document / live class / quiz chapter item. */
    public static function updateLesson(CourseChapterItem $chapterItem, array $data): void
    {
        $chapterItem->update([
            'chapter_id' => $data['chapter'] ?? null,
        ]);

        if ($data['type'] == 'lesson') {
            $courseChapterLesson = CourseChapterLesson::where('chapter_item_id', $chapterItem->id)->first();

            $old_file_path = $courseChapterLesson->file_path;
            if (in_array($courseChapterLesson->storage, ['wasabi', 'aws']) && $old_file_path != ($data['link_path'] ?? null)) {
                $disk = Storage::disk($courseChapterLesson->storage);
                $disk->exists($old_file_path) && $disk->delete($old_file_path);
            }

            $courseChapterLesson->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'course_id' => $chapterItem->course_id,
                'chapter_id' => $chapterItem->chapter_id,
                'chapter_item_id' => $chapterItem->id,
                'file_path' => $data['source'] == 'upload' ? ($data['upload_path'] ?? null) : ($data['link_path'] ?? null),
                'storage' => $data['source'],
                'file_type' => $data['file_type'],
                'volume' => $data['volume'] ?? null,
                'duration' => $data['duration'],
                'is_free' => $data['is_free'] ?? null,
            ]);
        } elseif ($data['type'] == 'live') {
            $courseChapterLesson = CourseChapterLesson::where('chapter_item_id', $chapterItem->id)->first();
            $courseChapterLesson->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'chapter_id' => $chapterItem->chapter_id,
                'chapter_item_id' => $chapterItem->id,
                'duration' => $data['duration'],
                'storage' => $data['source'] ? $data['source'] : 'live',
                'file_type' => $data['source'] ? 'video' : 'live',
                'file_path' => $data['source'] ? ($data['source'] == 'upload' ? ($data['upload_path'] ?? null) : ($data['link_path'] ?? null)) : null,
            ]);

            $start_time = date('Y-m-d H:i:s', strtotime($data['start_time']));
            $join_url = ($data['live_type'] ?? null) === 'zoom' ? ($data['join_url'] ?? null) : null;
            CourseLiveClass::where('lesson_id', $courseChapterLesson->id)->update([
                'start_time' => $start_time,
                'meeting_id' => $data['meeting_id'],
                'password' => $data['password'] ?? null,
                'join_url' => $join_url,
                'type' => $data['live_type'],
            ]);

            if (($data['student_mail_sent'] ?? null) == 'on') {
                static::sendLiveClassMail($courseChapterLesson, $start_time, $join_url);
            }
        } elseif ($data['type'] == 'document') {
            $courseChapterLesson = CourseChapterLesson::where('chapter_item_id', $chapterItem->id)->first();
            $courseChapterLesson->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'course_id' => $chapterItem->course_id,
                'chapter_id' => $chapterItem->chapter_id,
                'chapter_item_id' => $chapterItem->id,
                'file_path' => $data['upload_path'] ?? null,
                'file_type' => $data['file_type'],
            ]);
        } else {
            $quiz = Quiz::where('chapter_item_id', $chapterItem->id)->first();
            $quiz->update([
                'chapter_item_id' => $chapterItem->id,
                'title' => $data['title'],
                'time' => $data['time_limit'] ?? null,
                'attempt' => $data['attempts'] ?? null,
                'pass_mark' => $data['pass_mark'],
                'total_mark' => $data['total_mark'],
            ]);
        }
    }

    public static function sortLessons(string $chapterId, array $orderIds): void
    {
        foreach ($orderIds as $key => $itemId) {
            $chapterItem = CourseChapterItem::where(['chapter_id' => $chapterId, 'id' => $itemId])->first();
            $chapterItem->order = $key + 1;
            $chapterItem->save();
        }
    }

    /** Delete a chapter item with its quiz / lesson + files. */
    public static function destroyChapterItem(CourseChapterItem $chapterItem): void
    {
        // Compare the raw attribute: the model casts `type` to an enum,
        // so $chapterItem->type is an enum instance, not the string 'quiz'.
        if (($chapterItem->getAttributes()['type'] ?? $chapterItem->type) === 'quiz') {
            $quiz = $chapterItem->quiz;
            $question = $quiz->questions;
            foreach ($question as $key => $question) {
                $question->answers()->delete();
                $question->delete();
            }
            $quiz->delete();
            $chapterItem->delete();
        } else {
            if (in_array($chapterItem->lesson->storage, ['wasabi', 'aws'])) {
                $disk = Storage::disk($chapterItem->lesson->storage);
                $filePath = $chapterItem->lesson->file_path;
                $disk->exists($filePath) && $disk->delete($filePath);
            }
            // delete chapter item lesson if file exists
            if (\File::exists(asset($chapterItem->lesson->file_path))) {
                \File::delete(asset($chapterItem->lesson->file_path));
            }

            // delete lesson row
            $chapterItem->lesson()->delete();
            $chapterItem->delete();
        }
    }

    protected static function sendLiveClassMail(CourseChapterLesson $lesson, string $start_time, ?string $join_url): void
    {
        $user_ids = Enrollment::where('course_id', $lesson->course_id)->pluck('user_id')->toArray();
        $users = User::select('name', 'email')->whereIn('id', $user_ids)->get();
        $data = (object) [
            'course' => Course::select('title')->where('id', $lesson->course_id)->first()->title,
            'lesson' => $lesson->title,
            'start_time' => formattedDateTime($start_time),
            'join_url' => $join_url,
        ];
        AppMailService::sendLiveClassNotificationMail($users, $data);
    }
}
