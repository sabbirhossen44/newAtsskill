<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategoryStoreRequest;
use App\Repositories\BlogCategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class BlogCategoryController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $categories = BlogCategoryRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.blog-category.index', ['categories' => $categories, 'items' => $categories]);
    }

    public function create()
    {
        return view('admin.blog-category.create', []);
    }

    public function store(BlogCategoryStoreRequest $request)
    {
        $data = $request->validated();

        BlogCategoryRepository::storeByRequest($data);

        return redirect()->route('admin.blog-category.index')->with('success', 'Blog category created successfully.');
    }

    public function edit($id)
    {
        $item = BlogCategoryRepository::findByIdOrFail($id);
        $code = request('code') ?? getSessionLanguage();
                $languages = \App\Models\Language::all();
        return view('admin.blog-category.edit', ['item' => $item, 'category' => $item, 'code' => $code, 'languages' => $languages]);
    }

    public function update(BlogCategoryStoreRequest $request, $id)
    {
        $item = BlogCategoryRepository::findByIdOrFail($id);
        $data = $request->validated();

        BlogCategoryRepository::updateByRequest($item, $data);

        return redirect()->route('admin.blog-category.index')->with('success', 'Blog category updated successfully.');
    }

    public function destroy($id)
    {
        $item = BlogCategoryRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.blog-category.index')->with('success', 'Blog category deleted successfully.');
    }

    public function statusUpdate(Request $request, $id)
    {
        $item = BlogCategoryRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            BlogCategoryRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with('success', __('Updated Successfully'));
        }

        BlogCategoryRepository::toggleStatus($item);

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
