<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FeaturedCourseSectionRequest;
use App\Repositories\FeaturedCourseSectionRepository;
use Illuminate\Http\Request;

class FeaturedCourseSectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = FeaturedCourseSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.featured-course-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        return view('admin.featured-course-section.create', []);
    }

    public function store(FeaturedCourseSectionRequest $request)
    {
        $data = $request->validated();
        foreach (['all_category_ids', 'category_one_ids', 'category_two_ids', 'category_three_ids', 'category_four_ids', 'category_five_ids'] as $key) { if (isset($data[$key])) { $data[$key] = json_encode($data[$key]); } }

        FeaturedCourseSectionRepository::storeByRequest($data);

        return redirect()->route('admin.featured-course-section.index')->with('success', 'Featured course section created successfully.');
    }

    public function edit($id)
    {
        $item = FeaturedCourseSectionRepository::findByIdOrFail($id);
        return view('admin.featured-course-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(FeaturedCourseSectionRequest $request, $id)
    {
        $item = FeaturedCourseSectionRepository::findByIdOrFail($id);
        $data = $request->validated();
        foreach (['all_category_ids', 'category_one_ids', 'category_two_ids', 'category_three_ids', 'category_four_ids', 'category_five_ids'] as $key) { if (isset($data[$key])) { $data[$key] = json_encode($data[$key]); } }

        FeaturedCourseSectionRepository::updateByRequest($item, $data);

        return redirect()->route('admin.featured-course-section.index')->with('success', 'Featured course section updated successfully.');
    }

    public function destroy($id)
    {
        $item = FeaturedCourseSectionRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.featured-course-section.index')->with('success', 'Featured course section deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
