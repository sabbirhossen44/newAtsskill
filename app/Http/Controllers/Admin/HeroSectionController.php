<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Repositories\HeroSectionRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;

class HeroSectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = HeroSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.hero-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        return view('admin.hero-section.create', []);
    }

    public function store(SectionRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['banner_image'] = UploadService::store($request->file('image'));
        }

        HeroSectionRepository::storeByRequest($data);

        return redirect()->route('admin.hero-section.index')->with('success', 'Hero section created successfully.');
    }

    public function edit($id)
    {
        $item = HeroSectionRepository::findByIdOrFail($id);
        return view('admin.hero-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(SectionRequest $request, $id)
    {
        $item = HeroSectionRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['banner_image'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->banner_image ?? null);
        }

        HeroSectionRepository::updateByRequest($item, $data);

        return redirect()->route('admin.hero-section.index')->with('success', 'Hero section updated successfully.');
    }

    public function destroy($id)
    {
        $item = HeroSectionRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.hero-section.index')->with('success', 'Hero section deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
