<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageBuilderRequest;
use App\Repositories\PageRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class PageBuilderController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('page.management');
        Paginator::useBootstrap();
        $pages = PageRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.page-builder.index', ['pages' => $pages, 'items' => $pages]);
    }

    public function create()
    {
        $this->checkAdminPermission('page.management');
        return view('admin.page-builder.create', []);
    }

    public function store(PageBuilderRequest $request)
    {
        $this->checkAdminPermission('page.management');
        $data = $request->validated();

        PageRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.page-builder.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('page.management');
        $item = PageRepository::findByIdOrFail($id);
        $code = request('code') ?? getSessionLanguage();
                $languages = \App\Models\Language::all();
        return view('admin.page-builder.edit', ['item' => $item, 'page' => $item, 'code' => $code, 'languages' => $languages]);
    }

    public function update(PageBuilderRequest $request, $id)
    {
        $this->checkAdminPermission('page.management');
        $item = PageRepository::findByIdOrFail($id);
        $data = $request->validated();

        PageRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.page-builder.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('page.management');
        $item = PageRepository::findByIdOrFail($id);

        PageRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.page-builder.index');
    }

    public function statusUpdate(Request $request, $id)
    {
        $this->checkAdminPermission('page.management');
        $item = PageRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            PageRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
        }

        PageRepository::toggleStatus($item);

        return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
    }
}
