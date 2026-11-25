<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\ChapterLessonRequest;
use App\Http\Requests\Frontend\ChapterStoreRequest;
use App\Http\Requests\Frontend\QuizQuestionRequest;
use App\Repositories\CourseChapterRepository;
use App\Repositories\CourseContentRepository;
use App\Repositories\QuizQuestionRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseContentController extends Controller
{
    public function chapterStore(ChapterStoreRequest $request, string $courseId): RedirectResponse
    {
        CourseChapterRepository::storeByRequest([
            'title' => $request->title,
            'course_id' => $courseId,
            'instructor_id' => auth('web')->id(),
            'status' => 'active',
        ]);

        return redirect()->back()->with(['messege' => __('Chapter created successfully'), 'alert-type' => 'success']);
    }

    public function chapterEdit(string $chapterId)
    {
        $chapter = CourseChapterRepository::findById($chapterId);

        return view('frontend.instructor-dashboard.course.partials.edit-section-modal', compact('chapter'))->render();
    }

    public function chapterUpdate(Request $request, string $chapterId)
    {
        $chapter = CourseChapterRepository::findByIdOrFail($chapterId);
        abort_if($chapter->instructor_id != auth('web')->user()->id, 403, __('unauthorized access'));
        CourseChapterRepository::updateTitle($chapter, $request->title);

        return redirect()->back()->with(['messege' => __('Updated successfully'), 'alert-type' => 'success']);
    }

    public function chapterDestroy(string $chapterId)
    {
        $chapter = CourseChapterRepository::findByIdOrFail($chapterId);
        abort_if($chapter->instructor_id != auth('web')->user()->id, 403, __('unauthorized access'));
        CourseChapterRepository::destroyWithContent($chapter);

        return response()->json(['status' => 'success', 'message' => __('Question deleted successfully')]);
    }

    public function chapterSorting(string $courseId)
    {
        $chapters = CourseChapterRepository::orderedForCourse($courseId);

        return view('frontend.instructor-dashboard.course.partials.chapter-sorting-index', compact('chapters', 'courseId'))->render();
    }

    public function chapterSortingStore(Request $request, string $courseId)
    {
        CourseChapterRepository::updateOrder($courseId, $request->chapter_ids);

        return redirect()->back()->with(['messege' => __('Updated successfully'), 'alert-type' => 'success']);
    }

    public function lessonCreate(Request $request)
    {
        $courseId = $request->courseId;
        $chapterId = $request->chapterId;
        $chapters = CourseContentRepository::chaptersForCourse($courseId);
        $type = $request->type;
        if ($request->type == 'lesson') {
            return view('frontend.instructor-dashboard.course.partials.lesson-create-modal', [
                'courseId' => $courseId,
                'chapterId' => $chapterId,
                'chapters' => $chapters,
                'type' => $type,
            ])->render();
        } elseif ($request->type == 'document') {
            return view('frontend.instructor-dashboard.course.partials.document-create-modal', [
                'courseId' => $courseId,
                'chapterId' => $chapterId,
                'chapters' => $chapters,
                'type' => $type,
            ])->render();
        } elseif ($request->type == 'quiz') {
            return view('frontend.instructor-dashboard.course.partials.quiz-create-modal', [
                'courseId' => $courseId,
                'chapterId' => $chapterId,
                'chapters' => $chapters,
                'type' => $type,
            ])->render();
        } elseif ($request->type == 'live') {
            return view('frontend.instructor-dashboard.course.partials.live-create-modal', [
                'courseId' => $courseId,
                'chapterId' => $chapterId,
                'chapters' => $chapters,
                'type' => $type,
            ])->render();
        }
    }

    public function lessonStore(ChapterLessonRequest $request)
    {
        CourseContentRepository::storeLesson($request->validated(), auth('web')->id());

        return response()->json(['status' => 'success', 'message' => __('Lesson created successfully')]);
    }

    public function lessonEdit(Request $request)
    {
        $courseId = $request->courseId;
        $chapterItemId = $request->chapterItemId;
        $chapterItem = CourseContentRepository::findChapterItem($chapterItemId);
        $chapters = CourseContentRepository::chaptersForCourse($courseId);

        if ($request->type == 'lesson') {
            return view('frontend.instructor-dashboard.course.partials.lesson-edit-modal', [
                'chapters' => $chapters,
                'courseId' => $courseId,
                'chapterItem' => $chapterItem,
            ])->render();
        } elseif ($request->type == 'document') {
            return view('frontend.instructor-dashboard.course.partials.document-edit-modal', [
                'chapters' => $chapters,
                'courseId' => $courseId,
                'chapterItem' => $chapterItem,
            ])->render();
        } elseif ($request->type == 'live') {
            return view('frontend.instructor-dashboard.course.partials.live-edit-modal', [
                'chapters' => $chapters,
                'courseId' => $courseId,
                'chapterItem' => $chapterItem,
            ])->render();
        } else {
            return view('frontend.instructor-dashboard.course.partials.quiz-edit-modal', [
                'chapters' => $chapters,
                'courseId' => $courseId,
                'chapterItem' => $chapterItem,
            ])->render();
        }
    }

    public function lessonUpdate(ChapterLessonRequest $request)
    {
        $chapterItem = CourseContentRepository::findChapterItemOrFail($request->chapter_item_id);
        abort_if($chapterItem->instructor_id != auth('web')->user()->id, 403, __('unauthorized access'));
        CourseContentRepository::updateLesson($chapterItem, $request->validated());

        return response()->json(['status' => 'success', 'message' => __('Lesson updated successfully')]);
    }

    public function sortLessons(Request $request, string $chapterId)
    {
        CourseContentRepository::sortLessons($chapterId, $request->orderIds);

        return response()->json(['status' => 'success', 'message' => __('Lesson sorted successfully')]);
    }

    public function chapterLessonDestroy(string $chapterItemId)
    {
        $chapterItem = CourseContentRepository::findChapterItemOrFail($chapterItemId);
        abort_if($chapterItem->instructor_id != auth('web')->user()->id, 403, __('unauthorized access'));
        CourseContentRepository::destroyChapterItem($chapterItem);

        return response()->json(['status' => 'success', 'message' => __('Lesson deleted successfully')]);
    }

    public function createQuizQuestion(string $quizId)
    {
        return view('frontend.instructor-dashboard.course.partials.quiz-question-create-modal', ['quizId' => $quizId])->render();
    }

    public function storeQuizQuestion(QuizQuestionRequest $request, string $quizId)
    {
        QuizQuestionRepository::storeByRequest($quizId, $request->validated());

        return response()->json(['status' => 'success', 'message' => __('Question created successfully')]);
    }

    public function editQuizQuestion(string $questionId)
    {
        $question = QuizQuestionRepository::findByIdOrFail($questionId);

        return view('frontend.instructor-dashboard.course.partials.quiz-question-edit-modal', ['question' => $question])->render();
    }

    public function updateQuizQuestion(QuizQuestionRequest $request, string $questionId)
    {
        $question = QuizQuestionRepository::findByIdOrFail($questionId);
        QuizQuestionRepository::updateByRequest($question, $request->validated());

        return response()->json(['status' => 'success', 'message' => __('Question updated successfully')]);
    }

    public function destroyQuizQuestion(string $questionId)
    {
        $question = QuizQuestionRepository::findByIdOrFail($questionId);
        QuizQuestionRepository::destroy($question);

        return response()->json(['status' => 'success', 'message' => __('Question deleted successfully')]);
    }
}
