<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogPostRequest;
use App\Repositories\BlogRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class BlogController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('blog.view');
        Paginator::useBootstrap();
        $blogs = BlogRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.blogs.index', ['blogs' => $blogs, 'items' => $blogs]);
    }

    public function create()
    {
        $this->checkAdminPermission('blog.view');
        $categories = \App\Models\BlogCategory::all();
        return view('admin.blogs.create', ['categories' => $categories]);
    }

    public function store(BlogPostRequest $request)
    {
        $this->checkAdminPermission('blog.view');
        $data = $request->validated();
        $data['blog_category_id'] = $data['category_id'] ?? $data['blog_category_id'] ?? null;
                unset($data['category_id']);
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'));
        }

        BlogRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.blogs.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('blog.view');
        $item = BlogRepository::findByIdOrFail($id);
        $code = request('code') ?? getSessionLanguage();
                $categories = \App\Models\BlogCategory::all();
                $languages = \App\Models\Language::all();
        return view('admin.blogs.edit', ['item' => $item, 'blog' => $item, 'code' => $code, 'categories' => $categories, 'languages' => $languages]);
    }

    public function update(BlogPostRequest $request, $id)
    {
        $this->checkAdminPermission('blog.view');
        $item = BlogRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['blog_category_id'] = $data['category_id'] ?? $data['blog_category_id'] ?? null;
                unset($data['category_id']);
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->image ?? null);
        }

        BlogRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.blogs.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('blog.view');
        $item = BlogRepository::findByIdOrFail($id);

        BlogRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.blogs.index');
    }

    public function statusUpdate(Request $request, $id)
    {
        $this->checkAdminPermission('blog.view');
        $item = BlogRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            BlogRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
        }

        BlogRepository::toggleStatus($item);

        return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
    }
}
