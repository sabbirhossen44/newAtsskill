<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\InstructorCourseDeleteRequest;
use App\Http\Requests\Frontend\InstructorCourseFinishRequest;
use App\Http\Requests\Frontend\InstructorCourseMoreInfoRequest;
use App\Http\Requests\Frontend\InstructorCourseStoreRequest;
use App\Repositories\CourseChapterRepository;
use App\Repositories\CourseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class InstructorCourseController extends Controller
{
    public function index(): View
    {
        $courses = CourseRepository::paginateForInstructor(auth()->id());

        return view('frontend.instructor-dashboard.course.index', compact('courses'));
    }

    public function create()
    {
        return view('frontend.instructor-dashboard.course.create');
    }

    public function editView(string $id)
    {
        Session::put('course_create', $id);
        $course = CourseRepository::findByIdOrFail((int) $id);
        $editMode = true;

        return view('frontend.instructor-dashboard.course.create', compact('course', 'editMode'));
    }

    public function store(InstructorCourseStoreRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $request->input('slug');
        $data['edit_mode'] = $request->input('edit_mode');
        $data['id'] = $request->input('id');

        $course = CourseRepository::storeWizard($data, auth('web')->user()->id);

        // save course id in session
        Session::put('course_create', $course->id);

        return response()->json([
            'status' => 'success',
            'message' => __('Updated successfully'),
            'redirect' => route('instructor.courses.edit', ['id' => $course->id, 'step' => $request->next_step]),
        ]);
    }

    public function edit(Request $request)
    {
        if (! Session::get('course_create')) {
            return redirect(route('instructor.courses.create'));
        }

        switch (request('step')) {
            case '1':
                $course = CourseRepository::findByIdOrFail((int) $request->id);
                $editMode = true;

                return view('frontend.instructor-dashboard.course.create', compact('course', 'editMode'));
            case '2':
                return view('frontend.instructor-dashboard.course.more-information', CourseRepository::moreInfoData((int) request('id')));
                break;
            case '3':
                $chapters = CourseChapterRepository::orderedWithItemsForCourse($request->id);

                return view('frontend.instructor-dashboard.course.course-content', compact('chapters'));
                break;
            case '4':
                $courseId = request('id');
                $course = CourseRepository::findByIdOrFail((int) $courseId);

                return view('frontend.instructor-dashboard.course.finish', compact('course'));
                break;
            default:
                break;
        }
    }

    public function update(Request $request)
    {
        switch ($request->step) {
            case '2':
                $validated = $request->validate((new InstructorCourseMoreInfoRequest)->rules());
                $course = CourseRepository::findByIdOrFail((int) $request->course_id);
                abort_if($course->instructor_id != auth('web')->user()->id, 403);
                CourseRepository::updateMoreInfo($course, array_merge($validated, $request->only([
                    'course_id', 'capacity', 'qna', 'downloadable', 'certificate',
                    'partner_instructor', 'partner_instructors', 'levels', 'languages',
                ])));

                return response()->json([
                    'status' => 'success',
                    'message' => __('Updated Successfully'),
                    'redirect' => route('instructor.courses.edit', ['id' => Session::get('course_create'), 'step' => $request->next_step]),
                ]);
            case '3':
                return response()->json([
                    'status' => 'success',
                    'message' => __('Updated successfully'),
                    'redirect' => route('instructor.courses.edit', ['id' => Session::get('course_create'), 'step' => $request->next_step]),
                ]);
            case '4':
                $validated = $request->validate((new InstructorCourseFinishRequest)->rules());
                $course = CourseRepository::findByIdOrFail((int) $request->course_id);
                abort_if($course->instructor_id != auth('web')->user()->id, 403, __('unauthorized access'));
                CourseRepository::updateFinish($course, $validated);

                return response()->json([
                    'status' => 'success',
                    'message' => __('Course Updated Successfully'),
                    'redirect' => $request->next_step == 4 ? route('instructor.courses.index') : route('instructor.courses.edit', ['id' => Session::get('course_create'), 'step' => $request->next_step]),
                ]);

            default:
                break;
        }
    }

    public function getInstructors(Request $request)
    {
        $instructors = CourseRepository::instructorsForPartner($request->q, auth()->id());

        return response()->json($instructors);
    }

    public function getFiltersByCategory(string $id)
    {
        return view('frontend.instructor-dashboard.course.partials.filters', CourseRepository::filtersForCategory($id))->render();
    }

    public function showDeleteRequest(Request $request, $id)
    {
        return view('frontend.instructor-dashboard.course.partials.course-delete-request-modal', compact('id'));
    }

    public function sendDeleteRequest(InstructorCourseDeleteRequest $request)
    {
        $course = CourseRepository::findByIdOrFail((int) $request->course_id);
        if ($course->instructor_id != auth('web')->user()->id) {
            abort(403);
        }

        if (! CourseRepository::createDeleteRequest($course, $request->message)) {
            return redirect()->back()->with(['messege' => __('you already have a pending request for this course'), 'alert-type' => 'error']);
        }

        return redirect()->back()->with(['messege' => __('Request sent successfully'), 'alert-type' => 'success']);
    }
}
