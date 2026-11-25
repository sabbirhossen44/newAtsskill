<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Repositories\FeaturedInstructorSectionRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class FeaturedInstructorSectionController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $sections = FeaturedInstructorSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.featured-instructor-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        return view('admin.featured-instructor-section.create', []);
    }

    public function store(SectionRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'));
        }

        FeaturedInstructorSectionRepository::storeByRequest($data);

        return redirect()->route('admin.featured-instructor-section.index')->with('success', 'Featured instructor section created successfully.');
    }

    public function edit($id)
    {
        $item = FeaturedInstructorSectionRepository::findByIdOrFail($id);
        return view('admin.featured-instructor-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(SectionRequest $request, $id)
    {
        $item = FeaturedInstructorSectionRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->image ?? null);
        }

        FeaturedInstructorSectionRepository::updateByRequest($item, $data);

        return redirect()->route('admin.featured-instructor-section.index')->with('success', 'Featured instructor section updated successfully.');
    }

    public function destroy($id)
    {
        $item = FeaturedInstructorSectionRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.featured-instructor-section.index')->with('success', 'Featured instructor section deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
