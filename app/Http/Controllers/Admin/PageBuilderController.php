<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageBuilderRequest;
use App\Repositories\PageRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class PageBuilderController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $pages = PageRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.page-builder.index', ['pages' => $pages, 'items' => $pages]);
    }

    public function create()
    {
        return view('admin.page-builder.create', []);
    }

    public function store(PageBuilderRequest $request)
    {
        $data = $request->validated();

        PageRepository::storeByRequest($data);

        return redirect()->route('admin.page-builder.index')->with('success', 'Page created successfully.');
    }

    public function edit($id)
    {
        $item = PageRepository::findByIdOrFail($id);
        $code = request('code') ?? getSessionLanguage();
                $languages = \App\Models\Language::all();
        return view('admin.page-builder.edit', ['item' => $item, 'page' => $item, 'code' => $code, 'languages' => $languages]);
    }

    public function update(PageBuilderRequest $request, $id)
    {
        $item = PageRepository::findByIdOrFail($id);
        $data = $request->validated();

        PageRepository::updateByRequest($item, $data);

        return redirect()->route('admin.page-builder.index')->with('success', 'Page updated successfully.');
    }

    public function destroy($id)
    {
        $item = PageRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.page-builder.index')->with('success', 'Page deleted successfully.');
    }

    public function statusUpdate(Request $request, $id)
    {
        $item = PageRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            PageRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with('success', __('Updated Successfully'));
        }

        PageRepository::toggleStatus($item);

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
