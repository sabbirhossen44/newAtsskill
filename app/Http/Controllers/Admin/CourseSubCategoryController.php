<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Enums\RedirectTypeEnum;
use App\Http\Requests\Admin\CourseSubCategoryRequest;
use App\Models\CourseCategory;
use App\Repositories\CourseCategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

/**
 * Course sub-categories: children of a parent category. Routes keep
 * the old two-parameter URIs (parent_id / sub_category_id); the
 * rebuilt index view passes both ids for edit/destroy links.
 */
class CourseSubCategoryController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request, $parent_id)
    {
        $this->checkAdminPermission('course.management');
        Paginator::useBootstrap();
        $parentId = (int) $parent_id;
        $subCategories = CourseCategoryRepository::paginateSubCategories($parentId, $this->perPage($request));
        $parentCategories = CourseCategory::whereNull('parent_id')->get();

        return view('admin.course-sub-category.index', ['subCategories' => $subCategories, 'items' => $subCategories, 'parentId' => $parentId, 'parentCategories' => $parentCategories]);
    }

    public function create($parent_id)
    {
        $this->checkAdminPermission('course.management');
        $parentId = (int) $parent_id;
        $parentCategories = CourseCategory::whereNull('parent_id')->get();

        return view('admin.course-sub-category.create', ['parentId' => $parentId, 'parentCategories' => $parentCategories]);
    }

    public function store(CourseSubCategoryRequest $request, $parent_id)
    {
        $this->checkAdminPermission('course.management');
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = \App\Services\UploadService::store($request->file('icon'));
        }
        CourseCategoryRepository::storeSubCategory($data, (int) $parent_id);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.course-sub-category.index', ['parent_id' => (int) $parent_id]);
    }

    public function edit($parent_id, $sub_category_id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseCategoryRepository::findByIdOrFail((int) $sub_category_id);
        $parentId = (int) $parent_id;
        $parentCategories = CourseCategory::whereNull('parent_id')->get();

        return view('admin.course-sub-category.edit', ['item' => $item, 'category' => $item, 'parentId' => $parentId, 'parentCategories' => $parentCategories]);
    }

    public function update(CourseSubCategoryRequest $request, $parent_id, $sub_category_id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseCategoryRepository::findByIdOrFail((int) $sub_category_id);
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = \App\Services\UploadService::store($request->file('icon'), 'uploads/custom-images/', $item->icon ?? null);
        }
        CourseCategoryRepository::updateSubCategory($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.course-sub-category.index', ['parent_id' => (int) $parent_id]);
    }

    public function destroy($parent_id, $sub_category_id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseCategoryRepository::findByIdOrFail((int) $sub_category_id);
        CourseCategoryRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.course-sub-category.index', ['parent_id' => (int) $parent_id]);
    }

    public function statusUpdate(Request $request, $id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseCategoryRepository::findByIdOrFail((int) $id);
        if ($request->filled('status')) {
            CourseCategoryRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
        }
        CourseCategoryRepository::toggleStatus($item);

        return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
    }
}
