<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
    public function index(Request $request, $parent_id)
    {
        Paginator::useBootstrap();
        $parentId = (int) $parent_id;
        $subCategories = CourseCategoryRepository::paginateSubCategories($parentId, $this->perPage($request));
        $parentCategories = CourseCategory::whereNull('parent_id')->get();

        return view('admin.course-sub-category.index', ['subCategories' => $subCategories, 'items' => $subCategories, 'parentId' => $parentId, 'parentCategories' => $parentCategories]);
    }

    public function create($parent_id)
    {
        $parentId = (int) $parent_id;
        $parentCategories = CourseCategory::whereNull('parent_id')->get();

        return view('admin.course-sub-category.create', ['parentId' => $parentId, 'parentCategories' => $parentCategories]);
    }

    public function store(CourseSubCategoryRequest $request, $parent_id)
    {
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = \App\Services\UploadService::store($request->file('icon'));
        }
        CourseCategoryRepository::storeSubCategory($data, (int) $parent_id);

        return redirect()->route('admin.course-sub-category.index', ['parent_id' => (int) $parent_id])->with('success', 'Course sub-category created successfully.');
    }

    public function edit($parent_id, $sub_category_id)
    {
        $item = CourseCategoryRepository::findByIdOrFail((int) $sub_category_id);
        $parentId = (int) $parent_id;
        $parentCategories = CourseCategory::whereNull('parent_id')->get();

        return view('admin.course-sub-category.edit', ['item' => $item, 'category' => $item, 'parentId' => $parentId, 'parentCategories' => $parentCategories]);
    }

    public function update(CourseSubCategoryRequest $request, $parent_id, $sub_category_id)
    {
        $item = CourseCategoryRepository::findByIdOrFail((int) $sub_category_id);
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = \App\Services\UploadService::store($request->file('icon'), 'uploads/custom-images/', $item->icon ?? null);
        }
        CourseCategoryRepository::updateSubCategory($item, $data);

        return redirect()->route('admin.course-sub-category.index', ['parent_id' => (int) $parent_id])->with('success', 'Course sub-category updated successfully.');
    }

    public function destroy($parent_id, $sub_category_id)
    {
        $item = CourseCategoryRepository::findByIdOrFail((int) $sub_category_id);
        $item->delete();

        return redirect()->route('admin.course-sub-category.index', ['parent_id' => (int) $parent_id])->with('success', 'Course sub-category deleted successfully.');
    }

    public function statusUpdate(Request $request, $id)
    {
        $item = CourseCategoryRepository::findByIdOrFail((int) $id);
        if ($request->filled('status')) {
            CourseCategoryRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with('success', __('Updated Successfully'));
        }
        CourseCategoryRepository::toggleStatus($item);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
        }

        return back()->with('success', __('Updated Successfully'));
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
