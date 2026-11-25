<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\LessonReplyRequest;
use App\Repositories\LessonQnaRepository;
use Illuminate\Http\Request;

class InstructorLessonQnaController extends Controller
{
    public function index(Request $request)
    {
        $lesson_questions = LessonQnaRepository::paginateQuestions($request->only(['seen', 'sort_by']));

        return view('frontend.instructor-dashboard.lesson-qna.index', compact('lesson_questions'));
    }

    public function destroyQuestion($id)
    {
        if (LessonQnaRepository::destroyQuestion($id)) {
            $notification = ['messege' => __('Question deleted successfully'), 'alert-type' => 'success'];

            return back()->with($notification);
        }

        $notification = ['messege' => __('Something went wrong'), 'alert-type' => 'error'];

        return back()->with($notification);
    }

    public function createReply(LessonReplyRequest $request, $id)
    {
        LessonQnaRepository::createReply($id, auth('web')->user()->id, $request->reply);

        $notification = ['messege' => __('Reply created successfully'), 'alert-type' => 'success'];

        return back()->with($notification);
    }

    public function destroyReply($id)
    {
        if (LessonQnaRepository::destroyReply($id)) {
            $notification = ['messege' => __('Reply deleted successfully'), 'alert-type' => 'success'];

            return back()->with($notification);
        }

        $notification = ['messege' => __('Something went wrong'), 'alert-type' => 'error'];

        return back()->with($notification);
    }

    public function markAsReadUnread($id)
    {
        $question = LessonQnaRepository::toggleSeen($id);

        $notification = __('Updated Successfully');

        return response()->json([
            'success' => true,
            'title' => $question->seen == 1 ? __('Mark read') : __('Mark as unread'),
            'message' => $notification,
        ]);
    }
}
