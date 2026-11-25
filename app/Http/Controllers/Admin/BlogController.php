<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogPostRequest;
use App\Repositories\BlogRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $blogs = BlogRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.blogs.index', ['blogs' => $blogs, 'items' => $blogs]);
    }

    public function create()
    {
        $categories = \App\Models\BlogCategory::all();
        return view('admin.blogs.create', ['categories' => $categories]);
    }

    public function store(BlogPostRequest $request)
    {
        $data = $request->validated();
        $data['blog_category_id'] = $data['category_id'] ?? $data['blog_category_id'] ?? null;
                unset($data['category_id']);
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'));
        }

        BlogRepository::storeByRequest($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog created successfully.');
    }

    public function edit($id)
    {
        $item = BlogRepository::findByIdOrFail($id);
        $code = request('code') ?? getSessionLanguage();
                $categories = \App\Models\BlogCategory::all();
                $languages = \App\Models\Language::all();
        return view('admin.blogs.edit', ['item' => $item, 'blog' => $item, 'code' => $code, 'categories' => $categories, 'languages' => $languages]);
    }

    public function update(BlogPostRequest $request, $id)
    {
        $item = BlogRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['blog_category_id'] = $data['category_id'] ?? $data['blog_category_id'] ?? null;
                unset($data['category_id']);
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->image ?? null);
        }

        BlogRepository::updateByRequest($item, $data);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog updated successfully.');
    }

    public function destroy($id)
    {
        $item = BlogRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.blogs.index')->with('success', 'Blog deleted successfully.');
    }

    public function statusUpdate(Request $request, $id)
    {
        $item = BlogRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            BlogRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with('success', __('Updated Successfully'));
        }

        BlogRepository::toggleStatus($item);

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
