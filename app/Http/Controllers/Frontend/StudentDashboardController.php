<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\CourseRepository;
use App\Repositories\CourseReviewRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\OrderRepository;
use App\Repositories\QuizResultRepository;
use App\Services\StudentCertificateService;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function index(): View
    {
        $userId = auth('web')->user()->id;

        $totalEnrolledCourses = EnrollmentRepository::countForUser($userId);
        $totalQuizAttempts = QuizResultRepository::countForUser($userId);
        $totalReviews = CourseReviewRepository::countForUser($userId);
        $orders = OrderRepository::latestForBuyer($userId, 10);

        return view('frontend.student-dashboard.index', compact(
            'totalEnrolledCourses',
            'totalQuizAttempts',
            'totalReviews',
            'orders'
        ));
    }

    function enrolledCourses()
    {
        $enrolls = EnrollmentRepository::paginateForUser(auth('web')->user()->id, 10);

        return view('frontend.student-dashboard.enrolled-courses.index', compact('enrolls'));
    }

    function quizAttempts()
    {
        Session::forget('course_slug');
        $quizAttempts = QuizResultRepository::paginateForUser(auth('web')->user()->id, 10);

        return view('frontend.student-dashboard.quiz-attempts.index', compact('quizAttempts'));
    }

    function downloadCertificate(string $id)
    {
        $course = CourseRepository::findWithTrashedById((int) $id);

        if (! $course || ! StudentCertificateService::streamCertificateForCourse(auth('web')->user(), $course)) {
            return abort(404);
        }

        return redirect()->back();
    }

    function downloadDemoCertificate()
    {
        StudentCertificateService::streamDemoCertificate();

        return redirect()->back();
    }
}
