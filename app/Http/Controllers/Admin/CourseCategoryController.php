<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseCategoryStoreRequest;
use App\Http\Requests\Admin\CourseCategoryUpdateRequest;
use App\Repositories\CourseCategoryRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;

class CourseCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = CourseCategoryRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.course-category.index', ['categories' => $categories, 'items' => $categories]);
    }

    public function create()
    {
        $parentCategories = \App\Models\CourseCategory::whereNull('parent_id')->get();
        return view('admin.course-category.create', ['parentCategories' => $parentCategories]);
    }

    public function store(CourseCategoryStoreRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = UploadService::store($request->file('icon'));
        }

        CourseCategoryRepository::storeByRequest($data);

        return redirect()->route('admin.course-category.index')->with('success', 'Course category created successfully.');
    }

    public function edit($id)
    {
        $item = CourseCategoryRepository::findByIdOrFail($id);
        $code = request('code') ?? getSessionLanguage();
                $languages = \App\Models\Language::all();
                $parentCategories = \App\Models\CourseCategory::whereNull('parent_id')->where('id', '!=', $item->id)->get();
        return view('admin.course-category.edit', ['item' => $item, 'category' => $item, 'code' => $code, 'languages' => $languages, 'parentCategories' => $parentCategories]);
    }

    public function update(CourseCategoryUpdateRequest $request, $id)
    {
        $item = CourseCategoryRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = UploadService::store($request->file('icon'), 'uploads/custom-images/', $item->icon ?? null);
        }

        CourseCategoryRepository::updateByRequest($item, $data);

        return redirect()->route('admin.course-category.index')->with('success', 'Course category updated successfully.');
    }

    public function destroy($id)
    {
        $item = CourseCategoryRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.course-category.index')->with('success', 'Course category deleted successfully.');
    }

    public function statusUpdate(Request $request, $id)
    {
        $item = CourseCategoryRepository::findByIdOrFail($id);

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
