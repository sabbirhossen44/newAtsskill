<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Repositories\BannerSectionRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;

class BannerSectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = BannerSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.banner-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        return view('admin.banner-section.create', []);
    }

    public function store(SectionRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['bg_image'] = UploadService::store($request->file('image'));
        }

        BannerSectionRepository::storeByRequest($data);

        return redirect()->route('admin.banner-section.index')->with('success', 'Banner section created successfully.');
    }

    public function edit($id)
    {
        $item = BannerSectionRepository::findByIdOrFail($id);
        return view('admin.banner-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(SectionRequest $request, $id)
    {
        $item = BannerSectionRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['bg_image'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->bg_image ?? null);
        }

        BannerSectionRepository::updateByRequest($item, $data);

        return redirect()->route('admin.banner-section.index')->with('success', 'Banner section updated successfully.');
    }

    public function destroy($id)
    {
        $item = BannerSectionRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.banner-section.index')->with('success', 'Banner section deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
