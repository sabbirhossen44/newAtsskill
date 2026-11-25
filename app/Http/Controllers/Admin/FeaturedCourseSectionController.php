<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FeaturedCourseSectionRequest;
use App\Repositories\FeaturedCourseSectionRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class FeaturedCourseSectionController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('section.management');
        Paginator::useBootstrap();
        $sections = FeaturedCourseSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.featured-course-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        $this->checkAdminPermission('section.management');
        return view('admin.featured-course-section.create', []);
    }

    public function store(FeaturedCourseSectionRequest $request)
    {
        $this->checkAdminPermission('section.management');
        $data = $request->validated();
        foreach (['all_category_ids', 'category_one_ids', 'category_two_ids', 'category_three_ids', 'category_four_ids', 'category_five_ids'] as $key) { if (isset($data[$key])) { $data[$key] = json_encode($data[$key]); } }

        FeaturedCourseSectionRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.featured-course-section.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('section.management');
        $item = FeaturedCourseSectionRepository::findByIdOrFail($id);
        return view('admin.featured-course-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(FeaturedCourseSectionRequest $request, $id)
    {
        $this->checkAdminPermission('section.management');
        $item = FeaturedCourseSectionRepository::findByIdOrFail($id);
        $data = $request->validated();
        foreach (['all_category_ids', 'category_one_ids', 'category_two_ids', 'category_three_ids', 'category_four_ids', 'category_five_ids'] as $key) { if (isset($data[$key])) { $data[$key] = json_encode($data[$key]); } }

        FeaturedCourseSectionRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.featured-course-section.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('section.management');
        $item = FeaturedCourseSectionRepository::findByIdOrFail($id);

        FeaturedCourseSectionRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.featured-course-section.index');
    }
}
