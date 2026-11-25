<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseLevelRequest;
use App\Repositories\CourseLevelRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CourseLevelController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('course.management');
        Paginator::useBootstrap();
        $levels = CourseLevelRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.course-level.index', ['levels' => $levels, 'items' => $levels]);
    }

    public function create()
    {
        $this->checkAdminPermission('course.management');
        return view('admin.course-level.create', []);
    }

    public function store(CourseLevelRequest $request)
    {
        $this->checkAdminPermission('course.management');
        $data = $request->validated();

        CourseLevelRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.course-level.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseLevelRepository::findByIdOrFail($id);
        return view('admin.course-level.edit', ['item' => $item, 'level' => $item]);
    }

    public function update(CourseLevelRequest $request, $id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseLevelRepository::findByIdOrFail($id);
        $data = $request->validated();

        CourseLevelRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.course-level.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseLevelRepository::findByIdOrFail($id);

        CourseLevelRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.course-level.index');
    }

    public function statusUpdate(Request $request, $id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseLevelRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            CourseLevelRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
        }

        CourseLevelRepository::toggleStatus($item);

        return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
    }
}
