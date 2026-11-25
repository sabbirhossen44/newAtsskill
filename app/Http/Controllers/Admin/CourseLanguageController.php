<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseLanguageRequest;
use App\Repositories\CourseLanguageRepository;
use Illuminate\Http\Request;

class CourseLanguageController extends Controller
{
    public function index(Request $request)
    {
        $languages = CourseLanguageRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.course-language.index', ['languages' => $languages, 'items' => $languages]);
    }

    public function create()
    {
        return view('admin.course-language.create', []);
    }

    public function store(CourseLanguageRequest $request)
    {
        $data = $request->validated();
        unset($data['code']);

        CourseLanguageRepository::storeByRequest($data);

        return redirect()->route('admin.course-language.index')->with('success', 'Course language created successfully.');
    }

    public function edit($id)
    {
        $item = CourseLanguageRepository::findByIdOrFail($id);
        return view('admin.course-language.edit', ['item' => $item, 'language' => $item]);
    }

    public function update(CourseLanguageRequest $request, $id)
    {
        $item = CourseLanguageRepository::findByIdOrFail($id);
        $data = $request->validated();
        unset($data['code']);

        CourseLanguageRepository::updateByRequest($item, $data);

        return redirect()->route('admin.course-language.index')->with('success', 'Course language updated successfully.');
    }

    public function destroy($id)
    {
        $item = CourseLanguageRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.course-language.index')->with('success', 'Course language deleted successfully.');
    }

    public function statusUpdate(Request $request, $id)
    {
        $item = CourseLanguageRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            CourseLanguageRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with('success', __('Updated Successfully'));
        }

        CourseLanguageRepository::toggleStatus($item);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
        }

        return back()->with('success', __('Updated Successfully'));
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
