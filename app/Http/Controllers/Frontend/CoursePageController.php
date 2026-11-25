<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\CourseCategoryRepository;
use App\Repositories\CourseRepository;
use App\Repositories\CourseReviewRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoursePageController extends Controller
{
    function index(): View
    {
        return view('frontend.pages.courses.index', CourseRepository::filterOptions());
    }

    function fetchCourses(Request $request)
    {
        $courses = CourseRepository::paginateFiltered($request);

        $lastPage = $courses->lastPage();
        $page = $request->page ?? 1;
        $itemCount = $courses->count();
        $data = [
            'items' => view('frontend.partials.course-card', compact('courses'))->render(),
            'lastPage' => $lastPage,
            'currentPage' => $page,
            'itemCount' => $itemCount,
        ];

        // if main category is selected then show sub category card
        if ($request->main_category && $request->filled('main_category')) {
            $subCategories = CourseCategoryRepository::subCategoriesForMainCategory($request->main_category);

            $categoriesIds = is_array($request->category) ? $request->category : explode(',', $request->category);
            $data['sidebar_items'] = view('frontend.partials.course-sidebar-item', compact('subCategories', 'categoriesIds'))->render();
        }

        return response()->json($data);
    }

    function show(string $slug)
    {
        $course = CourseRepository::findBySlugWithDetailsOrFail($slug);
        $courseLessonCount = CourseRepository::lessonCount($course);
        $courseQuizCount = CourseRepository::quizCount($course);
        $reviews = CourseReviewRepository::paginateForCoursePage($course);

        return view('frontend.pages.courses.show', compact('course', 'courseLessonCount', 'courseQuizCount', 'reviews'));
    }
}
