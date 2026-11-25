<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Repositories\NewsletterSectionRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;

class NewsletterSectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = NewsletterSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.newsletter-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        return view('admin.newsletter-section.create', []);
    }

    public function store(SectionRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'));
        }

        NewsletterSectionRepository::storeByRequest($data);

        return redirect()->route('admin.newsletter-section.index')->with('success', 'Newsletter section created successfully.');
    }

    public function edit($id)
    {
        $item = NewsletterSectionRepository::findByIdOrFail($id);
        return view('admin.newsletter-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(SectionRequest $request, $id)
    {
        $item = NewsletterSectionRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->image ?? null);
        }

        NewsletterSectionRepository::updateByRequest($item, $data);

        return redirect()->route('admin.newsletter-section.index')->with('success', 'Newsletter section updated successfully.');
    }

    public function destroy($id)
    {
        $item = NewsletterSectionRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.newsletter-section.index')->with('success', 'Newsletter section deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
