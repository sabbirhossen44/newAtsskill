<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Repositories\FaqRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $faqs = FaqRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.faq.index', ['faqs' => $faqs, 'items' => $faqs]);
    }

    public function create()
    {
        return view('admin.faq.create', []);
    }

    public function store(FaqRequest $request)
    {
        $data = $request->validated();

        FaqRepository::storeByRequest($data);

        return redirect()->route('admin.faq.index')->with('success', 'FAQ created successfully.');
    }

    public function edit($id)
    {
        $item = FaqRepository::findByIdOrFail($id);
        return view('admin.faq.edit', ['item' => $item, 'faq' => $item]);
    }

    public function update(FaqRequest $request, $id)
    {
        $item = FaqRepository::findByIdOrFail($id);
        $data = $request->validated();

        FaqRepository::updateByRequest($item, $data);

        return redirect()->route('admin.faq.index')->with('success', 'FAQ updated successfully.');
    }

    public function destroy($id)
    {
        $item = FaqRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.faq.index')->with('success', 'FAQ deleted successfully.');
    }

    public function statusUpdate(Request $request, $id)
    {
        $item = FaqRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            FaqRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with('success', __('Updated Successfully'));
        }

        FaqRepository::toggleStatus($item);

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
