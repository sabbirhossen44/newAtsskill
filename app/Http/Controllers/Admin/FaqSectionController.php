<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Repositories\FaqSectionRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class FaqSectionController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('section.management');
        Paginator::useBootstrap();
        $sections = FaqSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.faq-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        $this->checkAdminPermission('section.management');
        return view('admin.faq-section.create', []);
    }

    public function store(SectionRequest $request)
    {
        $this->checkAdminPermission('section.management');
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'));
        }

        FaqSectionRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.faq-section.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('section.management');
        $item = FaqSectionRepository::findByIdOrFail($id);
        return view('admin.faq-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(SectionRequest $request, $id)
    {
        $this->checkAdminPermission('section.management');
        $item = FaqSectionRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->image ?? null);
        }

        FaqSectionRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.faq-section.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('section.management');
        $item = FaqSectionRepository::findByIdOrFail($id);

        FaqSectionRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.faq-section.index');
    }
}
