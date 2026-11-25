<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Repositories\ContactSectionRepository;
use Illuminate\Http\Request;

class ContactSectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = ContactSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.contact-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        return view('admin.contact-section.create', []);
    }

    public function store(SectionRequest $request)
    {
        $data = $request->validated();

        ContactSectionRepository::storeByRequest($data);

        return redirect()->route('admin.contact-section.index')->with('success', 'Contact section created successfully.');
    }

    public function edit($id)
    {
        $item = ContactSectionRepository::findByIdOrFail($id);
        return view('admin.contact-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(SectionRequest $request, $id)
    {
        $item = ContactSectionRepository::findByIdOrFail($id);
        $data = $request->validated();

        ContactSectionRepository::updateByRequest($item, $data);

        return redirect()->route('admin.contact-section.index')->with('success', 'Contact section updated successfully.');
    }

    public function destroy($id)
    {
        $item = ContactSectionRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.contact-section.index')->with('success', 'Contact section deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
