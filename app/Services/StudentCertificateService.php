<?php

namespace App\Services;

use App\Models\CertificateBuilder;
use App\Models\CertificateBuilderItem;
use App\Models\Course;
use App\Models\CourseChapterItem;
use App\Models\CourseProgress;
use App\Models\User;
use Carbon\Carbon;
use Dompdf\Dompdf;

/**
 * Student completion certificates: the Dompdf flow previously
 * inline in StudentDashboardController.
 */
class StudentCertificateService
{
    public static function lectureCount(Course $course): int
    {
        return CourseChapterItem::query()
            ->whereHas('chapter', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->count();
    }

    public static function completedLectureCount(User $user, Course $course): int
    {
        return CourseProgress::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('watched', 1)
            ->count();
    }

    public static function completedDate(User $user, Course $course): ?Carbon
    {
        $latest = CourseProgress::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('watched', 1)
            ->latest()
            ->first();

        return $latest?->created_at;
    }

    public static function completionPercent(User $user, Course $course): float
    {
        $lectureCount = static::lectureCount($course);

        return $lectureCount > 0
            ? (static::completedLectureCount($user, $course) / $lectureCount) * 100
            : 0;
    }

    public static function isComplete(User $user, Course $course): bool
    {
        return static::completionPercent($user, $course) == 100;
    }

    public static function renderHtml(string $studentName, string $platformName, string $courseTitle, string $date, string $instructorName): string
    {
        $certificate = CertificateBuilder::first();
        $certificateItems = CertificateBuilderItem::all();

        $html = view('frontend.student-dashboard.certificate.index', compact('certificateItems', 'certificate'))->render();

        $html = str_replace('[student_name]', $studentName, $html);
        $html = str_replace('[platform_name]', $platformName, $html);
        $html = str_replace('[course]', $courseTitle, $html);
        $html = str_replace('[date]', $date, $html);
        $html = str_replace('[instructor_name]', $instructorName, $html);

        return $html;
    }

    public static function streamPdf(string $html, string $fileName): void
    {
        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($fileName);
    }

    /**
     * Stream the completion certificate for a finished course.
     * Returns false when the course is not 100% complete
     * (the controller then aborts with 404, as before).
     */
    public static function streamCertificateForCourse(User $user, Course $course): bool
    {
        if (! static::isComplete($user, $course)) {
            return false;
        }

        $completedDate = static::completedDate($user, $course);

        static::streamPdf(
            static::renderHtml(
                $user->name,
                setting('app_name'),
                $course->title,
                Carbon::parse($completedDate)->format('d M Y'),
                $course->instructor->name,
            ),
            'certificate.pdf',
        );

        return true;
    }

    public static function streamDemoCertificate(): void
    {
        static::streamPdf(
            static::renderHtml(
                auth('web')->user()->name,
                (SettingService::all()['app_name'] ?? 'ATS Skill'),
                'Demo IT Certification Course',
                Carbon::parse(now()->format('d M Y')),
                'ATS Instructor',
            ),
            'demo-certificate.pdf',
        );
    }
}
