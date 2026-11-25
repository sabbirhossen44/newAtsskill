<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Requests\Frontend\QnaQuestionRequest;
use App\Http\Requests\Frontend\QnaReplyRequest;
use App\Repositories\LessonQuestionRepository;
use App\Repositories\LessonReplyRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QnaController extends Controller
{
    public function create(QnaQuestionRequest $request)
    {
        $question = LessonQuestionRepository::storeByRequest([
            'user_id' => auth('web')->user()->id,
            'lesson_id' => $request->lesson_id,
            'course_id' => $request->course_id,
            'question_title' => $request->question,
            'question_description' => $request->description,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => __('Question created successfully'),
            'question' => $question,
        ], 200);
    }

    public function fetchLessonQuestions(Request $request)
    {
        $questions = LessonQuestionRepository::paginateForLesson($request);

        return response()->json([
            'view' => view('frontend.pages.learning-player.partials.question-card', compact('questions'))->render(),
            'page' => $request->page,
            'last_page' => $questions->lastPage(),
            'data_count' => $questions->count(),
        ]);
    }

    public function createReply(QnaReplyRequest $request)
    {
        $reply = LessonReplyRepository::storeByRequest([
            'user_id' => auth('web')->user()->id,
            'question_id' => $request->question_id,
            'reply' => $request->reply,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => __('Reply created successfully'),
            'question' => $reply,
        ], 200);
    }

    public function fetchReply(Request $request)
    {
        $question = LessonQuestionRepository::findWithUser((int) $request->question_id);
        $replies = LessonReplyRepository::forQuestion((int) $request->question_id);

        return view('frontend.pages.learning-player.partials.reply-card', compact('replies', 'question'))->render();
    }

    public function destroyReply(string $id)
    {
        if (LessonReplyRepository::deleteOwnedByUser($id, auth('web')->user()->id)) {
            return response()->json([
                'status' => 'success',
                'message' => __('Reply deleted successfully'),
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('Something went wrong'),
        ]);
    }

    public function destroyQuestion(string $id)
    {
        if (LessonQuestionRepository::deleteOwnedByUser($id, auth('web')->user()->id)) {
            return response()->json([
                'status' => 'success',
                'message' => __('Question deleted successfully'),
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('Something went wrong'),
        ]);
    }
}
