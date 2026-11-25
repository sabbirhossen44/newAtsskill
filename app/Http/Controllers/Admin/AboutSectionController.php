<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Repositories\AboutSectionRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;

class AboutSectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = AboutSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.about-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        return view('admin.about-section.create', []);
    }

    public function store(SectionRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'));
        }

        AboutSectionRepository::storeByRequest($data);

        return redirect()->route('admin.about-section.index')->with('success', 'About section created successfully.');
    }

    public function edit($id)
    {
        $item = AboutSectionRepository::findByIdOrFail($id);
        return view('admin.about-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(SectionRequest $request, $id)
    {
        $item = AboutSectionRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->image ?? null);
        }

        AboutSectionRepository::updateByRequest($item, $data);

        return redirect()->route('admin.about-section.index')->with('success', 'About section updated successfully.');
    }

    public function destroy($id)
    {
        $item = AboutSectionRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.about-section.index')->with('success', 'About section deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
