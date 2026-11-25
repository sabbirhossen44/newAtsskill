<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseCategoryStoreRequest;
use App\Http\Requests\Admin\CourseCategoryUpdateRequest;
use App\Repositories\CourseCategoryRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CourseCategoryController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('course.management');
        Paginator::useBootstrap();
        $categories = CourseCategoryRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.course-category.index', ['categories' => $categories, 'items' => $categories]);
    }

    public function create()
    {
        $this->checkAdminPermission('course.management');
        $parentCategories = \App\Models\CourseCategory::whereNull('parent_id')->get();
        return view('admin.course-category.create', ['parentCategories' => $parentCategories]);
    }

    public function store(CourseCategoryStoreRequest $request)
    {
        $this->checkAdminPermission('course.management');
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = UploadService::store($request->file('icon'));
        }

        CourseCategoryRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.course-category.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseCategoryRepository::findByIdOrFail($id);
        $code = request('code') ?? getSessionLanguage();
                $languages = \App\Models\Language::all();
                $parentCategories = \App\Models\CourseCategory::whereNull('parent_id')->where('id', '!=', $item->id)->get();
        return view('admin.course-category.edit', ['item' => $item, 'category' => $item, 'code' => $code, 'languages' => $languages, 'parentCategories' => $parentCategories]);
    }

    public function update(CourseCategoryUpdateRequest $request, $id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseCategoryRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = UploadService::store($request->file('icon'), 'uploads/custom-images/', $item->icon ?? null);
        }

        CourseCategoryRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.course-category.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseCategoryRepository::findByIdOrFail($id);

        CourseCategoryRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.course-category.index');
    }

    public function statusUpdate(Request $request, $id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseCategoryRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            CourseCategoryRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
        }

        CourseCategoryRepository::toggleStatus($item);

        return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
    }
}
