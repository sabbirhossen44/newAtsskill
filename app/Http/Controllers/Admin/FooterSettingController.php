<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Repositories\FooterSettingRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class FooterSettingController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('footer.management');
        Paginator::useBootstrap();
        $footerSettings = FooterSettingRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.footersetting.index', ['footerSettings' => $footerSettings, 'items' => $footerSettings]);
    }

    public function create()
    {
        $this->checkAdminPermission('footer.management');
        return view('admin.footersetting.create', []);
    }

    public function store(SectionRequest $request)
    {
        $this->checkAdminPermission('footer.management');
        $data = $request->validated();
        $data['footer_text'] = $data['description'] ?? $data['footer_text'] ?? null;
                unset($data['title'], $data['description'], $data['status']);
        if ($request->hasFile('logo')) {
            $data['logo'] = UploadService::store($request->file('logo'));
        }

        FooterSettingRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.footersetting.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('footer.management');
        $item = FooterSettingRepository::findByIdOrFail($id);
        return view('admin.footersetting.edit', ['item' => $item, 'footerSetting' => $item]);
    }

    public function update(SectionRequest $request, $id)
    {
        $this->checkAdminPermission('footer.management');
        $item = FooterSettingRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['footer_text'] = $data['description'] ?? $data['footer_text'] ?? null;
                unset($data['title'], $data['description'], $data['status']);
        if ($request->hasFile('logo')) {
            $data['logo'] = UploadService::store($request->file('logo'), 'uploads/custom-images/', $item->logo ?? null);
        }

        FooterSettingRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.footersetting.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('footer.management');
        $item = FooterSettingRepository::findByIdOrFail($id);

        FooterSettingRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.footersetting.index');
    }
}
