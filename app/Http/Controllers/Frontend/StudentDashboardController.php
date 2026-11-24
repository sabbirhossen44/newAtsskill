<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseChapterItem;
use App\Models\CourseProgress;
use App\Models\CourseReview;
use App\Models\QuizResult;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use App\Models\CertificateBuilder;
use App\Models\CertificateBuilderItem;
use App\Models\Enrollment;
use App\Models\Order;

class StudentDashboardController extends Controller {
    public function index(): View {
        $totalEnrolledCourses = Enrollment::where('user_id', auth('web')->user()->id)->count();
        $totalQuizAttempts = QuizResult::where('user_id', auth('web')->user()->id)->count();
        $totalReviews = CourseReview::where('user_id', auth('web')->user()->id)->count();
        $orders = Order::where('buyer_id', auth('web')->user()->id)->orderBy('id', 'desc')->take(10)->get();
        return view('frontend.student-dashboard.index', compact(
            'totalEnrolledCourses',
            'totalQuizAttempts',
            'totalReviews',
            'orders'
        ));
    }

    function enrolledCourses() {
        $enrolls = Enrollment::with(['course' => function ($q) {
            $q->withTrashed();
        }])->where('user_id', auth('web')->user()->id)->orderByDesc('id')->paginate(10);
        return view('frontend.student-dashboard.enrolled-courses.index', compact('enrolls'));
    }

    function quizAttempts() {
        Session::forget('course_slug');
        $quizAttempts = QuizResult::with(['quiz'])->where('user_id', auth('web')->user()->id)->orderByDesc('id')->paginate(10);

        return view('frontend.student-dashboard.quiz-attempts.index', compact('quizAttempts'));
    }

    function downloadCertificate(string $id) {
        $certificate = CertificateBuilder::first();
        $certificateItems = CertificateBuilderItem::all();
        $course = Course::withTrashed()->find($id);

        $courseLectureCount = CourseChapterItem::whereHas('chapter', function ($q) use ($course) {
            $q->where('course_id', $course->id);
        })->count();

        $courseLectureCompletedByUser = CourseProgress::where('user_id', auth('web')->user()->id)
            ->where('course_id', $course->id)->where('watched', 1)->latest();

        $completed_date = \Carbon\Carbon::parse($courseLectureCompletedByUser->first()->format('d M Y')?->created_at);

        $courseLectureCompletedByUser = CourseProgress::where('user_id', auth('web')->user()->id)
            ->where('course_id', $course->id)->where('watched', 1)->count();

        $courseCompletedPercent = $courseLectureCount > 0 ? ($courseLectureCompletedByUser / $courseLectureCount) * 100 : 0;

        if ($courseCompletedPercent != 100) {
            return abort(404);
        }

        $html = view('frontend.student-dashboard.certificate.index', compact('certificateItems', 'certificate'))->render();

        $html = str_replace('[student_name]', auth('web')->user()->name, $html);
        $html = str_replace('[platform_name]', setting('app_name'), $html);
        $html = str_replace('[course]', $course->title, $html);
        $html = str_replace('[date]', \Carbon\Carbon::parse($completed_date)->format('d M Y'), $html);
        $html = str_replace('[instructor_name]', $course->instructor->name, $html);

        // Initialize Dompdf
        $dompdf = new Dompdf(array('enable_remote' => true));

        // Load HTML content
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();
        $dompdf->stream("certificate.pdf");
        return redirect()->back();
    }

    function downloadDemoCertificate() {
        $certificate = CertificateBuilder::first();
        $certificateItems = CertificateBuilderItem::all();

        $html = view('frontend.student-dashboard.certificate.index', compact('certificateItems', 'certificate'))->render();

        $html = str_replace('[student_name]', auth('web')->user()->name, $html);
        $html = str_replace('[platform_name]', (\App\Services\SettingService::all()['app_name'] ?? 'ATS Skill'), $html);
        $html = str_replace('[course]', 'Demo IT Certification Course', $html);
        $html = str_replace('[date]', \Carbon\Carbon::parse(now()->format('d M Y')), $html);
        $html = str_replace('[instructor_name]', 'ATS Instructor', $html);

        // Initialize Dompdf
        $dompdf = new Dompdf(array('enable_remote' => true));

        // Load HTML content
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();
        $dompdf->stream("demo-certificate.pdf");
        return redirect()->back();
    }
}
