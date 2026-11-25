<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategoryStoreRequest;
use App\Repositories\BlogCategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class BlogCategoryController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('blog.category.view');
        Paginator::useBootstrap();
        $categories = BlogCategoryRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.blog-category.index', ['categories' => $categories, 'items' => $categories]);
    }

    public function create()
    {
        $this->checkAdminPermission('blog.category.view');
        return view('admin.blog-category.create', []);
    }

    public function store(BlogCategoryStoreRequest $request)
    {
        $this->checkAdminPermission('blog.category.view');
        $data = $request->validated();

        BlogCategoryRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.blog-category.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('blog.category.view');
        $item = BlogCategoryRepository::findByIdOrFail($id);
        $code = request('code') ?? getSessionLanguage();
                $languages = \App\Models\Language::all();
        return view('admin.blog-category.edit', ['item' => $item, 'category' => $item, 'code' => $code, 'languages' => $languages]);
    }

    public function update(BlogCategoryStoreRequest $request, $id)
    {
        $this->checkAdminPermission('blog.category.view');
        $item = BlogCategoryRepository::findByIdOrFail($id);
        $data = $request->validated();

        BlogCategoryRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.blog-category.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('blog.category.view');
        $item = BlogCategoryRepository::findByIdOrFail($id);

        BlogCategoryRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.blog-category.index');
    }

    public function statusUpdate(Request $request, $id)
    {
        $this->checkAdminPermission('blog.category.view');
        $item = BlogCategoryRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            BlogCategoryRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
        }

        BlogCategoryRepository::toggleStatus($item);

        return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
    }
}
