<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Repositories\FeaturedInstructorSectionRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class FeaturedInstructorSectionController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('section.management');
        Paginator::useBootstrap();
        $sections = FeaturedInstructorSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.featured-instructor-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        $this->checkAdminPermission('section.management');
        return view('admin.featured-instructor-section.create', []);
    }

    public function store(SectionRequest $request)
    {
        $this->checkAdminPermission('section.management');
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'));
        }

        FeaturedInstructorSectionRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.featured-instructor-section.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('section.management');
        $item = FeaturedInstructorSectionRepository::findByIdOrFail($id);
        return view('admin.featured-instructor-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(SectionRequest $request, $id)
    {
        $this->checkAdminPermission('section.management');
        $item = FeaturedInstructorSectionRepository::findByIdOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = UploadService::store($request->file('image'), 'uploads/custom-images/', $item->image ?? null);
        }

        FeaturedInstructorSectionRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.featured-instructor-section.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('section.management');
        $item = FeaturedInstructorSectionRepository::findByIdOrFail($id);

        FeaturedInstructorSectionRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.featured-instructor-section.index');
    }
}
