<?php

namespace App\Repositories;

use Arafat\LaravelRepository\Repository;

use App\Models\Admin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Spatie roles/permissions for the admin area, plus the admin-role
 * assignment screens (admins are App\Models\Admin rows).
 */
class RoleRepository extends Repository
{
    public static function model()
    {
        return Role::class;
    }

    public static function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return static::model()::query()->paginate($perPage);
    }

    public static function allRoles()
    {
        return static::model()::query()->get();
    }

    public static function allPermissions()
    {
        return Permission::query()->get();
    }

    public static function permissionGroups()
    {
        return DB::table('permissions')
            ->select('group_name as name')
            ->groupBy('group_name')
            ->get();
    }

    public static function storeByRequest(array $data): Role
    {
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);

        $role = static::model()::query()->create(['name' => $data['name']]);

        if (! empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role->fresh();
    }

    /**
     * Preserves the old controller quirk: the name and permissions are
     * only saved when at least one permission is submitted.
     */
    public static function updateByRequest(Role $role, array $data): bool
    {
        if (! empty($data['permissions'])) {
            $role->name = $data['name'];
            $role->save();
            $role->syncPermissions($data['permissions']);

            return true;
        }

        return false;
    }

    public static function activeAdmins()
    {
        return Admin::query()->whereStatus('active')->get();
    }

    /**
     * The <option> list for the assign-role AJAX lookup, as the old
     * RolesController::getAdminRoles built it.
     */
    public static function rolesOptionsForAdmin(?Admin $admin): string
    {
        $options = "<option value='' disabled>".__('Select Role').'</option>';

        if ($admin) {
            foreach (static::allRoles() as $role) {
                $options .= "<option value='{$role->name}' ".($admin->hasRole($role->name) ? 'selected' : '').">{$role->name}</option>";
            }
        }

        return $options;
    }

    public static function assignRoles(Admin $admin, array $roles): void
    {
        $admin->syncRoles($roles);
    }
}
