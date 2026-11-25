<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialRequest;
use App\Repositories\TestimonialRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class TestimonialController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('testimonial.view');
        Paginator::useBootstrap();
        $testimonials = TestimonialRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.testimonial.index', ['testimonials' => $testimonials, 'items' => $testimonials]);
    }

    public function create()
    {
        $this->checkAdminPermission('testimonial.view');
        return view('admin.testimonial.create', []);
    }

    public function store(TestimonialRequest $request)
    {
        $this->checkAdminPermission('testimonial.view');
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'));
        }

        TestimonialRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.testimonial.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('testimonial.view');
        $item = TestimonialRepository::findByIdOrFail($id);
        return view('admin.testimonial.edit', ['item' => $item, 'testimonial' => $item]);
    }

    public function update(TestimonialRequest $request, $id)
    {
        $this->checkAdminPermission('testimonial.view');
        $item = TestimonialRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->image ?? null);
        }

        TestimonialRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.testimonial.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('testimonial.view');
        $item = TestimonialRepository::findByIdOrFail($id);

        TestimonialRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.testimonial.index');
    }

    public function statusUpdate(Request $request, $id)
    {
        $this->checkAdminPermission('testimonial.view');
        $item = TestimonialRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            TestimonialRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
        }

        TestimonialRepository::toggleStatus($item);

        return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
    }
}
