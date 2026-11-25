<?php

namespace App\Services;

use App\Enums\LiveClassTypeEnum;
use App\Models\Course;
use App\Models\CourseChapterLesson;
use App\Models\JitsiSetting;
use App\Models\Quiz;
use App\Models\User;
use App\Repositories\CourseReviewRepository;
use App\Repositories\LearningRepository;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Storage;

/**
 * Learning-player logic moved out of LearningController: progress tracking,
 * lesson/live/document/quiz file info, quiz grading, reviews and the
 * Jitsi live-session JWT.
 */
class LearningService
{
    /** Player page data; creates the initial "current" progress row when needed. */
    public static function playerData(Course $course, User $user): array
    {
        \Illuminate\Support\Facades\Session::put('course_slug', $course->slug);
        \Illuminate\Support\Facades\Session::put('course_title', $course->title);

        $currentProgress = LearningRepository::currentProgress($course->id, $user->id);
        $alreadyWatchedLectures = LearningRepository::watchedLessonIds($course->id, $user->id, 'lesson');
        $alreadyCompletedQuiz = LearningRepository::watchedLessonIds($course->id, $user->id, 'quiz');
        $announcements = LearningRepository::announcementsForCourse($course->id);
        $courseLectureCount = LearningRepository::lectureCount($course->id);
        $courseLectureCompletedByUser = LearningRepository::completedCount($course->id, $user->id);
        $courseCompletedPercent = $courseLectureCount > 0 ? ($courseLectureCompletedByUser / $courseLectureCount) * 100 : 0;

        if (! $currentProgress) {
            $currentProgress = LearningRepository::ensureInitialProgress($course, $user->id);
        }

        return compact(
            'course',
            'currentProgress',
            'announcements',
            'courseCompletedPercent',
            'courseLectureCount',
            'courseLectureCompletedByUser',
            'alreadyWatchedLectures',
            'alreadyCompletedQuiz'
        );
    }

    /**
     * File info for a lesson / live class / document / quiz, after marking
     * the row as the current progress. Returns the JSON payload array.
     */
    public static function fileInfo(array $input, User $user): array
    {
        LearningRepository::setCurrentProgress($input, $user->id);

        if ($input['type'] == 'lesson') {
            $fileInfo = array_merge(LearningRepository::lessonFileInfo($input['lessonId'])->toArray(), ['type' => 'lesson']);
            if (in_array($fileInfo['storage'], ['wasabi', 'aws'])) {
                $fileInfo['file_path'] = Storage::disk($fileInfo['storage'])->url($fileInfo['file_path']);
            }

            return ['file_info' => $fileInfo];
        }

        if ($input['type'] == 'live') {
            $fileInfo = array_merge(LearningRepository::liveFileInfo($input['lessonId'])->toArray(), ['type' => 'live']);

            $now = Carbon::now();
            $startTime = Carbon::parse($fileInfo['live']['start_time']);
            $endTime = $startTime->clone()->addMinutes($fileInfo['duration']);
            $fileInfo['start_time'] = formattedDateTime($startTime);
            $fileInfo['end_time'] = formattedDateTime($endTime);
            $fileInfo['is_live_now'] = $now->between($startTime, $endTime);

            if ($now->lt($startTime)) {
                $fileInfo['is_live_now'] = 'not_started';
            } elseif ($now->between($startTime, $endTime)) {
                $fileInfo['is_live_now'] = 'started';
            } else {
                $fileInfo['is_live_now'] = 'ended';
            }

            return ['file_info' => $fileInfo];
        }

        if ($input['type'] == 'document') {
            $fileInfo = array_merge(LearningRepository::lessonFileInfo($input['lessonId'])->toArray(), ['type' => 'document']);
            if ('pdf' == $fileInfo['file_type']) {
                return [
                    'view' => view('frontend.pages.learning-player.partials.pdf-viewer', ['file_path' => $fileInfo['file_path']])->render(),
                    'file_info' => $fileInfo,
                ];
            }
            if ('docx' == $fileInfo['file_type']) {
                return [
                    'view' => view('frontend.pages.learning-player.partials.docx-viewer', ['file_path' => $fileInfo['file_path']])->render(),
                    'file_info' => $fileInfo,
                ];
            }

            return ['file_info' => $fileInfo];
        }

        $fileInfo = array_merge(Quiz::findOrFail($input['lessonId'])->toArray(), ['type' => 'quiz']);

        return ['file_info' => $fileInfo];
    }

    /**
     * Mark a lesson watched/unwatched. Returns the JSON payload, or null
     * when there is no progress row and the lesson is being un-watched
     * (the old code returned nothing in that case).
     */
    public static function markLessonComplete(array $input, User $user): ?array
    {
        $progress = LearningRepository::progressForLesson($input['lessonId'], $user->id, $input['type']);

        if ($progress) {
            $progress->watched = $input['status'];
            $progress->save();

            return ['status' => 'success', 'message' => __('Updated successfully.')];
        }

        if ($input['status'] == 0) {
            return null;
        }

        return ['status' => 'error', 'message' => __('You didnt watched this lesson')];
    }

    public static function downloadableLesson(string $lessonId): CourseChapterLesson
    {
        return LearningRepository::findLessonOrFail($lessonId);
    }

    /** Grade a quiz submission and store the result. */
    public static function gradeQuiz(Quiz $quiz, array $answers, User $user): \App\Models\QuizResult
    {
        return LearningRepository::gradeQuiz($quiz, $answers, $user->id);
    }

    public static function storeReview(array $data, User $user): void
    {
        CourseReviewRepository::storeByRequest([
            'course_id' => $data['course_id'],
            'user_id' => $user->id,
            'rating' => $data['rating'],
            'review' => $data['review'],
        ]);
    }

    /**
     * Live-session data for a lesson. Returns ['type' => 'zoom', 'lesson' => ...]
     * for Zoom, ['type' => 'jitsi', 'lesson' => ..., 'title' => ..., 'jwt' => ...,
     * 'roomName' => ...] for Jitsi, or null when the Jitsi credential is missing.
     */
    public static function liveSessionData(string $lessonId, User $user): ?array
    {
        $lesson = LearningRepository::liveLesson($lessonId);

        if ($lesson->live->type === LiveClassTypeEnum::ZOOM) {
            return ['type' => 'zoom', 'lesson' => $lesson];
        }

        $jitsi_credential = JitsiSetting::where('instructor_id', $lesson->course->instructor_id)->first();

        if ($jitsi_credential) {
            $jwt = static::generateJwtToken($jitsi_credential, $user);
            $roomName = "{$jitsi_credential->app_id}/{$lesson->live->meeting_id}";

            return [
                'type' => 'jitsi',
                'lesson' => $lesson,
                'title' => $lesson->title,
                'jwt' => trim($jwt),
                'roomName' => $roomName,
            ];
        }

        return null;
    }

    /** Generate a JaaS JWT token (moved from LearningController). */
    public static function generateJwtToken(JitsiSetting $jitsi_credential, User $user): string
    {
        $instructor = $jitsi_credential->instructor_id == $user->id;

        $api_key = $jitsi_credential->api_key;
        $app_id = $jitsi_credential->app_id; // Your AppID (previously tenant)
        $user_email = $user->name;
        $user_name = $user->name;
        $user_is_moderator = $instructor;
        $user_avatar_url = ! empty($user->image) ? asset($user->image) : '';
        $user_id = $user->id;
        $live_streaming_enabled = $instructor;
        $recording_enabled = $instructor;
        $outbound_enabled = false;
        $transcription_enabled = false;
        $exp_delay = 7200;
        $nbf_delay = 0;

        // Read your private key from file
        $private_key = file_get_contents(storage_path("app/user_{$jitsi_credential->instructor_id}/rsb_private_key.pk"));

        $payload = [
            'iss' => 'chat',
            'aud' => 'jitsi',
            'exp' => time() + $exp_delay,
            'nbf' => time() - $nbf_delay,
            'room' => '*',
            'sub' => $app_id,
            'context' => [
                'user' => [
                    'moderator' => $user_is_moderator ? 'true' : 'false',
                    'email' => $user_email,
                    'name' => $user_name,
                    'avatar' => $user_avatar_url,
                    'id' => $user_id,
                ],
                'features' => [
                    'recording' => $recording_enabled ? 'true' : 'false',
                    'livestreaming' => $live_streaming_enabled ? 'true' : 'false',
                    'transcription' => $transcription_enabled ? 'true' : 'false',
                    'outbound-call' => $outbound_enabled ? 'true' : 'false',
                ],
            ],
        ];

        return JWT::encode($payload, $private_key, 'RS256', $api_key);
    }
}
