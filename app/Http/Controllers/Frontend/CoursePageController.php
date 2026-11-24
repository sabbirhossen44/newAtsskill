<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseChapter;
use App\Models\CourseChapterItem;
use App\Models\CourseChapterLesson;
use App\Models\CourseReview;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use App\Models\CourseCategory;
use App\Models\CourseLanguage;
use App\Models\CourseLevel;

class CoursePageController extends Controller
{
    function index() : View {
        // কোর্স পেজের ফিল্টার অপশনগুলো ২৪ ঘণ্টা (১৪৪০ মিনিট) ক্যাশ করে রাখা হলো
        $categories = Cache::remember('course_page_categories', 1440, function () {
            return CourseCategory::active()->whereNull('parent_id')->with(['translation'])->get();
        });

        $languages = Cache::remember('course_page_languages', 1440, function () {
            return CourseLanguage::where('status', 1)->get();
        });

        $levels = Cache::remember('course_page_levels', 1440, function () {
            return CourseLevel::where('status', 1)->with('translation')->get();
        });

        return view('frontend.pages.courses.index', compact('categories', 'languages', 'levels'));
    }


    function fetchCourses(Request $request) {
        $query = Course::query();
        $query->where(['is_approved' => 'approved', 'status' => 'active']);
        $query->whereHas('category', function($q) use ($request) {
            $q->where('status', 1);
        });

        $query->when($request->search, function($q) use ($request) {
            $q->where('title', 'like', '%'.$request->search.'%');
        });

        $query->when($request->main_category, function($q) use ($request) {
            $q->whereHas('category', function($q) use ($request) {
                $q->whereHas('parentCategory', function($q) use ($request) {
                    $q->where('slug', $request->main_category);
                });
            });
        });

        $query->when($request->category && $request->filled('category'), function($q) use ($request) {
            $categoriesIds = is_array($request->category) ? $request->category : explode(',', $request->category);
            $q->whereIn('category_id', $categoriesIds);
        });

        $query->when($request->language && $request->filled('language'), function($q) use ($request) {
            $languagesIds = is_array($request->language) ? $request->language : explode(',', $request->language);
            $q->whereHas('languages', function($q) use ($languagesIds) {
                $q->whereIn('language_id', $languagesIds);
            });
        });

        $query->when($request->price, function($q) use ($request) {
            if($request->price == 'paid') {
                $q->where('price', '>', 0);
            } else {
                $q->where('price', 0)->orWhere('price', null);
            }
        });

        $query->when($request->level, function($q) use ($request) {
            $levelsIds = is_array($request->level) ? $request->level : explode(',', $request->level);
            $q->whereHas('levels', function($q) use ($levelsIds) {
                $q->whereIn('level_id', $levelsIds);
            });
        });

        $query->with(['instructor:id,name', 'enrollments', 'category.translation']);
        $query->orderBy('created_at', $request->order && $request->filled('order') ? $request->order : 'desc');
        $courses = $query->paginate(12);

        $lastPage = $courses->lastPage();
        $page = $request->page ?? 1;
        $itemCount = $courses->count();
        $data = [
            'items' => view('frontend.partials.course-card', compact('courses'))->render(),
            'lastPage' => $lastPage,
            'currentPage' => $page,
            'itemCount' => $itemCount
        ];

        // if main category is selected then show sub category card
        if($request->main_category && $request->filled('main_category')) {
            $subCategories = CourseCategory::whereHas('parentCategory', function($q) use ($request) {
                $q->where('slug', $request->main_category);
            })->with('translation')->get();
            
            $categoriesIds = is_array($request->category) ? $request->category : explode(',', $request->category);
            $data['sidebar_items'] = view('frontend.partials.course-sidebar-item', compact('subCategories', 'categoriesIds'))->render();
        }

        return response()->json($data);
    }

    function show(string $slug) {
        // প্রতিটি কোর্সের ডিটেইলস স্লাগ অনুযায়ী ক্যাশ করে রাখা হলো যাতে বারবার ডাটাবেজে হিট না করে
        $course = Cache::remember('course_details_' . $slug, 1440, function () use ($slug) {
            return Course::active()->with(['chapters' => function($query) {
                $query->orderBy('order', 'asc')->with(['chapterItems', 'chapterItems.lesson', 'chapterItems.quiz']);
            }])
            ->withCount(['reviews' => function($query) {
                $query->where('status', 1)->whereHas('course')->whereHas('user');
            }])
            ->where('slug', $slug)->firstOrFail();
        });

        $courseLessonCount = Cache::remember('course_lesson_count_' . $slug, 1440, function () use ($course) {
            return CourseChapterLesson::where('course_id', $course->id)->count();
        });

        $courseQuizCount = Cache::remember('course_quiz_count_' . $slug, 1440, function () use ($course) {
            return Quiz::where('course_id', $course->id)->count();
        });

        $reviews = CourseReview::where('course_id', $course->id)->where('status', 1)->whereHas('course')->whereHas('user')->orderBy('created_at', 'desc')->paginate(20);
        
        return view('frontend.pages.courses.show', compact('course', 'courseLessonCount', 'courseQuizCount', 'reviews'));
    }
}