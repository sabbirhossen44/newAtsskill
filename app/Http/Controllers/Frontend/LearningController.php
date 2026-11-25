<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\LearningReviewRequest;
use App\Repositories\CourseRepository;
use App\Repositories\CourseReviewRepository;
use App\Repositories\LearningRepository;
use App\Repositories\QuizResultRepository;
use App\Services\LearningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LearningController extends Controller
{
    function index(string $slug)
    {
        $course = CourseRepository::findActiveBySlugWithContent($slug);

        return view('frontend.pages.learning-player.index', LearningService::playerData($course, auth('web')->user()));
    }

    function getFileInfo(Request $request)
    {
        return response()->json(LearningService::fileInfo($request->only(['courseId', 'chapterId', 'lessonId', 'type']), auth('web')->user()));
    }

    function makeLessonComplete(Request $request)
    {
        $result = LearningService::markLessonComplete($request->only(['lessonId', 'type', 'status']), auth('web')->user());

        return $result === null ? response('', 200) : response()->json($result);
    }

    function downloadResource(string $lessonId)
    {
        $resource = LearningService::downloadableLesson($lessonId);
        if (! \File::exists(public_path($resource->file_path))) {
            return redirect()->back()->with(['alert-type' => 'error', 'message' => __('Links is broke or some thing went wrong')]);
        }

        return response()->download(public_path($resource->file_path));
    }

    function quizIndex(string $id)
    {
        $attempt = QuizResultRepository::countAttempts(auth('web')->user()->id, (int) $id);
        $quiz = LearningRepository::findQuizOrFail($id);
        if ($attempt >= $quiz->attempt) {
            return redirect()->route('student.learning.index', Session::get('course_slug'))->with(['alert-type' => 'error', 'message' => __('You reached maximum attempt')]);
        }

        return view('frontend.pages.learning-player.quiz-index', compact('quiz', 'attempt'));
    }

    function quizStore(Request $request, string $id)
    {
        $quiz = LearningRepository::findQuizOrFail($id);
        $quizResult = LearningService::gradeQuiz($quiz, $request->question ?? [], auth('web')->user());

        return redirect()->route('student.quiz.result', ['id' => $id, 'result_id' => $quizResult->id]);
    }

    function quizResult(string $id, string $resultId)
    {
        $attempt = QuizResultRepository::countAttempts(auth('web')->user()->id, (int) $id);
        $quiz = LearningRepository::findQuizOrFail($id);
        $quizResult = LearningRepository::findQuizResultOrFail($resultId);

        return view('frontend.pages.learning-player.quiz-result', compact('quiz', 'attempt', 'quizResult'));
    }

    function addReview(LearningReviewRequest $request)
    {
        $review = CourseReviewRepository::findForUser($request->course_id, auth('web')->user()->id);
        if ($review) {
            return redirect()->back()->with(['alert-type' => 'error', 'message' => __('Already added review')]);
        }

        LearningService::storeReview($request->validated(), auth('web')->user());

        return redirect()->back()->with(['alert-type' => 'success', 'message' => __('Review added successfully')]);
    }

    function fetchReviews(Request $request, string $courseId)
    {
        $reviews = CourseReviewRepository::paginateForCourse((int) $courseId, (int) ($request->page ?? 1));

        return response()->json([
            'view' => view('frontend.pages.learning-player.partials.review-card', compact('reviews'))->render(),
            'page' => $request->page,
            'last_page' => $reviews->lastPage(),
            'data_count' => $reviews->count(),
        ]);
    }

    function liveSession(Request $request, string $slug, string $lesson_id)
    {
        $data = LearningService::liveSessionData($lesson_id, auth('web')->user());

        if ($data === null) {
            return back();
        }

        if ($data['type'] == 'zoom') {
            return view('frontend.pages.learning-player.partials.live.zoom', ['lesson' => $data['lesson']]);
        }

        return view('frontend.pages.learning-player.partials.live.jitsi', [
            'title' => $data['title'],
            'jwt' => $data['jwt'],
            'roomName' => $data['roomName'],
        ]);
    }
}
