<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignRoleRequest;
use App\Http\Requests\RoleFormRequest;
use App\Repositories\AdminRepository;
use App\Repositories\RoleRepository;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        $roles = RoleRepository::paginate($this->perPage($request));

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = RoleRepository::allPermissions();
        $permission_groups = RoleRepository::permissionGroups();

        return view('admin.roles.create', compact('permissions', 'permission_groups'));
    }

    public function store(RoleFormRequest $request)
    {
        RoleRepository::storeByRequest($request->validated());

        return redirect()->route('admin.role.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        // No dedicated show view exists (old code had none either);
        // the show route redirects to the edit screen.
        return redirect()->route('admin.role.edit', $role->id);
    }

    public function edit(Role $role)
    {
        $permissions = RoleRepository::allPermissions();
        $permission_groups = RoleRepository::permissionGroups();

        return view('admin.roles.edit', compact('permissions', 'permission_groups', 'role') + ['item' => $role]);
    }

    public function update(RoleFormRequest $request, Role $role)
    {
        RoleRepository::updateByRequest($role, $request->validated());

        return redirect()->route('admin.role.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->id === 1) {
            return back()->with('error', 'This role cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('admin.role.index')
            ->with('success', 'Role deleted successfully.');
    }

    public function assignRoleView()
    {
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
        $admin = AdminRepository::findByIdOrFail((int) $request->input('user_id'));

        RoleRepository::assignRoles($admin, $request->validated()['role']);

        return redirect()->route('admin.role.index')
            ->with('success', 'Role updated successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
