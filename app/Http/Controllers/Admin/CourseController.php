<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Enums\RedirectTypeEnum;
use App\Http\Requests\Admin\CourseStoreRequest;
use App\Models\CourseCategory;
use App\Models\User;
use App\Repositories\CourseRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

/**
 * Admin course CRUD (the simple single-page forms in the rebuilt
 * views). The old multi-step wizard lives in the frontend instructor
 * controllers; here store/update use the admin field set the views
 * post (title/slug/category_id/instructor_id/price/description/
 * thumbnail/type/status/...). CourseStoreRequest is NOT used: it
 * requires the wizard's `instructor` field, which these forms do not
 * send, so a plain Request with matching rules validates instead.
 */
class CourseController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:course_categories,id'],
            'instructor_id' => ['nullable', 'exists:users,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'certificate' => ['nullable'],
            'downloadable' => ['nullable'],
            'qna' => ['nullable'],
            'duration' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
        ];
    }

    public function index(Request $request)
    {
        $this->checkAdminPermission('course.management');
        Paginator::useBootstrap();
        $courses = CourseRepository::paginateAdmin($request->input('keyword'), $this->perPage($request));

        return view('admin.course.index', ['courses' => $courses, 'items' => $courses]);
    }

    public function create()
    {
        $this->checkAdminPermission('course.management');
        $categories = CourseCategory::all();
        $instructors = User::where('role', 'instructor')->get();

        return view('admin.course.create', compact('categories', 'instructors'));
    }

    public function store(Request $request)
    {
        $this->checkAdminPermission('course.management');
        $data = $request->validate($this->rules());
        $data['slug'] = $data['slug'] ?? \Illuminate\Support\Str::slug($data['title']);
        $request->validate(['slug' => 'unique:courses,slug']);

        CourseRepository::storeAdmin($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.course.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseRepository::findByIdOrFail($id);
        $categories = CourseCategory::all();
        $instructors = User::where('role', 'instructor')->get();

        return view('admin.course.edit', ['item' => $item, 'course' => $item, 'categories' => $categories, 'instructors' => $instructors]);
    }

    public function update(Request $request, $id)
    {
        $this->checkAdminPermission('course.management');
        $course = CourseRepository::findByIdOrFail($id);
        $data = $request->validate($this->rules());
        $request->validate(['slug' => 'unique:courses,slug,'.$id]);

        CourseRepository::updateAdmin($course, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.course.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('course.management');
        $course = CourseRepository::findByIdOrFail($id);
        $course->delete();

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.course.index');
    }

    public function statusUpdate(Request $request, $id)
    {
        $this->checkAdminPermission('course.management');
        $course = CourseRepository::findByIdOrFail($id);
        $status = $request->input('status');

        if ($status && CourseStatusEnum::tryFrom($status)) {
            $course->update(['status' => $status]);
        } else {
            $course->update(['status' => $course->status === CourseStatusEnum::ACTIVE ? CourseStatusEnum::INACTIVE : CourseStatusEnum::ACTIVE]);
        }

        return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
    }
}
