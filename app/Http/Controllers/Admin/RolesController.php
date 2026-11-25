<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignRoleRequest;
use App\Http\Requests\RoleFormRequest;
use App\Repositories\AdminRepository;
use App\Repositories\RoleRepository;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('role.view');
        $roles = RoleRepository::paginate($this->perPage($request));

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $this->checkAdminPermission('role.create');
        $permissions = RoleRepository::allPermissions();
        $permission_groups = RoleRepository::permissionGroups();

        return view('admin.roles.create', compact('permissions', 'permission_groups'));
    }

    public function store(RoleFormRequest $request)
    {
        $this->checkAdminPermission('role.store');

        RoleRepository::storeByRequest($request->validated());

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.role.index');
    }

    public function edit(Role $role)
    {
        $this->checkAdminPermission('role.edit');
        $permissions = RoleRepository::allPermissions();
        $permission_groups = RoleRepository::permissionGroups();

        return view('admin.roles.edit', compact('permissions', 'permission_groups', 'role'));
    }

    public function update(RoleFormRequest $request, Role $role)
    {
        $this->checkAdminPermission('role.update');

        RoleRepository::updateByRequest($role, $request->validated());

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.role.index');
    }

    public function destroy(Role $role)
    {
        $this->checkAdminPermission('role.delete');
        abort_if($role->id == 1, 403);

        RoleRepository::delete($role);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.role.index');
    }

    public function assignRoleView()
    {
        $this->checkAdminPermission('role.assign');
        $admins = RoleRepository::activeAdmins();
        $roles = RoleRepository::allRoles();

        return view('admin.roles.assign-role', compact('admins', 'roles'));
    }

    public function getAdminRoles($id)
    {
        $admin = AdminRepository::findById((int) $id);

        return response()->json([
            'success' => $admin !== null,
            'data' => RoleRepository::rolesOptionsForAdmin($admin),
        ]);
    }

    public function assignRoleUpdate(AssignRoleRequest $request)
    {
        $this->checkAdminPermission('role.assign');

        $admin = AdminRepository::findByIdOrFail((int) $request->input('user_id'));

        RoleRepository::assignRoles($admin, $request->validated()['role']);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.role.index');
    }
}
