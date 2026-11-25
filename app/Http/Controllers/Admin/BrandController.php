<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Repositories\BrandRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class BrandController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('brand.management');
        Paginator::useBootstrap();
        $brands = BrandRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.brand.index', ['brands' => $brands, 'items' => $brands]);
    }

    public function create()
    {
        $this->checkAdminPermission('brand.management');
        return view('admin.brand.create', []);
    }

    public function store(BrandRequest $request)
    {
        $this->checkAdminPermission('brand.management');
        $data = $request->validated();
        $data['status'] = ($data['status'] ?? null) === 'active' || ($data['status'] ?? null) === 1 || ($data['status'] ?? null) === '1' ? 1 : 0;
        if ($request->hasFile('logo')) {
            $data['image'] = UploadService::store($request->file('logo'));
        }
        // brands.image is NOT NULL; the old MySQL code relied on non-strict
        // mode inserting '' when no logo was uploaded.
        $data['image'] = $data['image'] ?? '';

        BrandRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.brand.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('brand.management');
        $item = BrandRepository::findByIdOrFail($id);
        return view('admin.brand.edit', ['item' => $item, 'brand' => $item]);
    }

    public function update(BrandRequest $request, $id)
    {
        $this->checkAdminPermission('brand.management');
        $item = BrandRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['status'] = ($data['status'] ?? null) === 'active' || ($data['status'] ?? null) === 1 || ($data['status'] ?? null) === '1' ? 1 : 0;
        if ($request->hasFile('logo')) {
            $data['image'] = UploadService::store($request->file('logo'), 'uploads/custom-images/', $item->image ?? null);
        }

        BrandRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.brand.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('brand.management');
        $item = BrandRepository::findByIdOrFail($id);

        BrandRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.brand.index');
    }

    public function statusUpdate(Request $request, $id)
    {
        $this->checkAdminPermission('brand.management');
        $item = BrandRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            BrandRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
        }

        BrandRepository::toggleStatus($item);

        return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
    }
}
