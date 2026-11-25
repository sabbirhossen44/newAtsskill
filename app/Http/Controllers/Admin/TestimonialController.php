<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialRequest;
use App\Repositories\TestimonialRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $testimonials = TestimonialRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.testimonial.index', ['testimonials' => $testimonials, 'items' => $testimonials]);
    }

    public function create()
    {
        return view('admin.testimonial.create', []);
    }

    public function store(TestimonialRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'));
        }

        TestimonialRepository::storeByRequest($data);

        return redirect()->route('admin.testimonial.index')->with('success', 'Testimonial created successfully.');
    }

    public function edit($id)
    {
        $item = TestimonialRepository::findByIdOrFail($id);
        return view('admin.testimonial.edit', ['item' => $item, 'testimonial' => $item]);
    }

    public function update(TestimonialRequest $request, $id)
    {
        $item = TestimonialRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->image ?? null);
        }

        TestimonialRepository::updateByRequest($item, $data);

        return redirect()->route('admin.testimonial.index')->with('success', 'Testimonial updated successfully.');
    }

    public function destroy($id)
    {
        $item = TestimonialRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.testimonial.index')->with('success', 'Testimonial deleted successfully.');
    }

    public function statusUpdate(Request $request, $id)
    {
        $item = TestimonialRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            TestimonialRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with('success', __('Updated Successfully'));
        }

        TestimonialRepository::toggleStatus($item);

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
