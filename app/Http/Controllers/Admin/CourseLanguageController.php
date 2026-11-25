<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseLanguageRequest;
use App\Repositories\CourseLanguageRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CourseLanguageController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('course.management');
        Paginator::useBootstrap();
        $languages = CourseLanguageRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.course-language.index', ['languages' => $languages, 'items' => $languages]);
    }

    public function create()
    {
        $this->checkAdminPermission('course.management');
        return view('admin.course-language.create', []);
    }

    public function store(CourseLanguageRequest $request)
    {
        $this->checkAdminPermission('course.management');
        $data = $request->validated();
        unset($data['code']);

        CourseLanguageRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.course-language.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseLanguageRepository::findByIdOrFail($id);
        return view('admin.course-language.edit', ['item' => $item, 'language' => $item]);
    }

    public function update(CourseLanguageRequest $request, $id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseLanguageRepository::findByIdOrFail($id);
        $data = $request->validated();
        unset($data['code']);

        CourseLanguageRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.course-language.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseLanguageRepository::findByIdOrFail($id);

        CourseLanguageRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.course-language.index');
    }

    public function statusUpdate(Request $request, $id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseLanguageRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            CourseLanguageRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
        }

        CourseLanguageRepository::toggleStatus($item);

        return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
    }
}
