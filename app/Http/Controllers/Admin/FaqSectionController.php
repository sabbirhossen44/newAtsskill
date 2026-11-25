<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Repositories\FaqSectionRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;

class FaqSectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = FaqSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.faq-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        return view('admin.faq-section.create', []);
    }

    public function store(SectionRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'));
        }

        FaqSectionRepository::storeByRequest($data);

        return redirect()->route('admin.faq-section.index')->with('success', 'FAQ section created successfully.');
    }

    public function edit($id)
    {
        $item = FaqSectionRepository::findByIdOrFail($id);
        return view('admin.faq-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(SectionRequest $request, $id)
    {
        $item = FaqSectionRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->image ?? null);
        }

        FaqSectionRepository::updateByRequest($item, $data);

        return redirect()->route('admin.faq-section.index')->with('success', 'FAQ section updated successfully.');
    }

    public function destroy($id)
    {
        $item = FaqSectionRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.faq-section.index')->with('success', 'FAQ section deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
