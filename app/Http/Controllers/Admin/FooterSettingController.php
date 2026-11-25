<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Repositories\FooterSettingRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;

class FooterSettingController extends Controller
{
    public function index(Request $request)
    {
        $footerSettings = FooterSettingRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.footersetting.index', ['footerSettings' => $footerSettings, 'items' => $footerSettings]);
    }

    public function create()
    {
        return view('admin.footersetting.create', []);
    }

    public function store(SectionRequest $request)
    {
        $data = $request->validated();
        $data['footer_text'] = $data['description'] ?? $data['footer_text'] ?? null;
                unset($data['title'], $data['description'], $data['status']);
        if ($request->hasFile('logo')) {
            $data['logo'] = UploadService::store($request->file('logo'));
        }

        FooterSettingRepository::storeByRequest($data);

        return redirect()->route('admin.footersetting.index')->with('success', 'Footer setting created successfully.');
    }

    public function edit($id)
    {
        $item = FooterSettingRepository::findByIdOrFail($id);
        return view('admin.footersetting.edit', ['item' => $item, 'footerSetting' => $item]);
    }

    public function update(SectionRequest $request, $id)
    {
        $item = FooterSettingRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['footer_text'] = $data['description'] ?? $data['footer_text'] ?? null;
                unset($data['title'], $data['description'], $data['status']);
        if ($request->hasFile('logo')) {
            $data['logo'] = UploadService::store($request->file('logo'), 'uploads/custom-images/', $item->logo ?? null);
        }

        FooterSettingRepository::updateByRequest($item, $data);

        return redirect()->route('admin.footersetting.index')->with('success', 'Footer setting updated successfully.');
    }

    public function destroy($id)
    {
        $item = FooterSettingRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.footersetting.index')->with('success', 'Footer setting deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
