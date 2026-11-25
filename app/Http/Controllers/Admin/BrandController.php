<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Repositories\BrandRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $brands = BrandRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.brand.index', ['brands' => $brands, 'items' => $brands]);
    }

    public function create()
    {
        return view('admin.brand.create', []);
    }

    public function store(BrandRequest $request)
    {
        $data = $request->validated();
        $data['status'] = ($data['status'] ?? null) === 'active' || ($data['status'] ?? null) === 1 || ($data['status'] ?? null) === '1' ? 1 : 0;
        if ($request->hasFile('logo')) {
            $data['image'] = UploadService::store($request->file('logo'));
        }
        // brands.image is NOT NULL; the old MySQL code relied on non-strict
        // mode inserting '' when no logo was uploaded.
        $data['image'] = $data['image'] ?? '';

        BrandRepository::storeByRequest($data);

        return redirect()->route('admin.brand.index')->with('success', 'Brand created successfully.');
    }

    public function edit($id)
    {
        $item = BrandRepository::findByIdOrFail($id);
        return view('admin.brand.edit', ['item' => $item, 'brand' => $item]);
    }

    public function update(BrandRequest $request, $id)
    {
        $item = BrandRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['status'] = ($data['status'] ?? null) === 'active' || ($data['status'] ?? null) === 1 || ($data['status'] ?? null) === '1' ? 1 : 0;
        if ($request->hasFile('logo')) {
            $data['image'] = UploadService::store($request->file('logo'), 'uploads/custom-images/', $item->image ?? null);
        }

        BrandRepository::updateByRequest($item, $data);

        return redirect()->route('admin.brand.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy($id)
    {
        $item = BrandRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.brand.index')->with('success', 'Brand deleted successfully.');
    }

    public function statusUpdate(Request $request, $id)
    {
        $item = BrandRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            BrandRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with('success', __('Updated Successfully'));
        }

        BrandRepository::toggleStatus($item);

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
