<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\CourseReviewRepository;

class StudentReviewController extends Controller
{
    function index()
    {
        $reviews = CourseReviewRepository::paginateForUser(auth('web')->user()->id, 30);

        return view('frontend.student-dashboard.review.index', compact('reviews'));
    }

    function show(string $id)
    {
        $review = CourseReviewRepository::findForUserOrFail((int) $id, auth('web')->user()->id);

        return view('frontend.student-dashboard.review.show', compact('review'));
    }

    function destroy(string $id)
    {
        CourseReviewRepository::deleteForUser((int) $id, auth('web')->user()->id);

        return response()->json(['status' => 'success', 'message' => __('Review deleted successfully')]);
    }
}
