<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseLevelRequest;
use App\Repositories\CourseLevelRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CourseLevelController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $levels = CourseLevelRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.course-level.index', ['levels' => $levels, 'items' => $levels]);
    }

    public function create()
    {
        return view('admin.course-level.create', []);
    }

    public function store(CourseLevelRequest $request)
    {
        $data = $request->validated();

        CourseLevelRepository::storeByRequest($data);

        return redirect()->route('admin.course-level.index')->with('success', 'Course level created successfully.');
    }

    public function edit($id)
    {
        $item = CourseLevelRepository::findByIdOrFail($id);
        return view('admin.course-level.edit', ['item' => $item, 'level' => $item]);
    }

    public function update(CourseLevelRequest $request, $id)
    {
        $item = CourseLevelRepository::findByIdOrFail($id);
        $data = $request->validated();

        CourseLevelRepository::updateByRequest($item, $data);

        return redirect()->route('admin.course-level.index')->with('success', 'Course level updated successfully.');
    }

    public function destroy($id)
    {
        $item = CourseLevelRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.course-level.index')->with('success', 'Course level deleted successfully.');
    }

    public function statusUpdate(Request $request, $id)
    {
        $item = CourseLevelRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            CourseLevelRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with('success', __('Updated Successfully'));
        }

        CourseLevelRepository::toggleStatus($item);

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
