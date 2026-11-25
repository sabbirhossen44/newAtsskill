<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Repositories\OurFeaturesSectionRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class OurFeaturesSectionController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $sections = OurFeaturesSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.our-features-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        return view('admin.our-features-section.create', []);
    }

    public function store(SectionRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image_one'] = UploadService::store($request->file('image'));
        }

        OurFeaturesSectionRepository::storeByRequest($data);

        return redirect()->route('admin.our-features-section.index')->with('success', 'Our features section created successfully.');
    }

    public function edit($id)
    {
        $item = OurFeaturesSectionRepository::findByIdOrFail($id);
        return view('admin.our-features-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(SectionRequest $request, $id)
    {
        $item = OurFeaturesSectionRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image_one'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->image_one ?? null);
        }

        OurFeaturesSectionRepository::updateByRequest($item, $data);

        return redirect()->route('admin.our-features-section.index')->with('success', 'Our features section updated successfully.');
    }

    public function destroy($id)
    {
        $item = OurFeaturesSectionRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.our-features-section.index')->with('success', 'Our features section deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
