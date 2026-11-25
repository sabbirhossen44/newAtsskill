<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use App\Repositories\AdminRepository;
use App\Repositories\RoleRepository;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('admin.view');

        $admins = AdminRepository::paginate($this->perPage($request));

        return view('admin.admin-list.index')->with([
            'admins' => $admins,
        ]);
    }

    public function create()
    {
        $this->checkAdminPermission('admin.create');
        $roles = RoleRepository::allRoles();

        return view('admin.admin-list.create', compact('roles'));
    }

    public function store(AdminRequest $request)
    {
        $this->checkAdminPermission('admin.store');

        AdminRepository::storeByRequest($request->validated());

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.admin.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('admin.edit');
        $admin = AdminRepository::findByIdOrFail($id);
        $roles = RoleRepository::allRoles();

        return view('admin.admin-list.edit', compact('roles', 'admin'));
    }

    public function update(AdminRequest $request, $id)
    {
        $this->checkAdminPermission('admin.update');
        $admin = AdminRepository::findByIdOrFail($id);
        abort_if($admin->id == 1, 403);

        AdminRepository::updateByRequest($admin, $request->validated());

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.admin.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('admin.delete');
        $admin = AdminRepository::findByIdOrFail($id);
        abort_if($admin->id == 1, 403);

        AdminRepository::delete($admin);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.admin.index');
    }

    public function changeStatus($id)
    {
        $this->checkAdminPermission('admin.update');
        $admin = AdminRepository::findByIdOrFail($id);
        abort_if($admin->id == 1, 403);

        AdminRepository::toggleStatus($admin);
        $notification = __('Updated Successfully');

        return response()->json([
            'success' => true,
            'message' => $notification,
        ]);
    }
}
