<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Spatie Laravel Permission
    |--------------------------------------------------------------------------
    |
    | Stock Spatie v6 configuration. Spatie has no separate "default
    | guard" key: the guard comes from the model (App\Models\Admin sets
    | $guard_name = 'admin') and from auth.defaults.guard ('web' for
    | frontend users). Permissions/roles are seeded with guard_name
    | 'admin' (database/seeders/RolePermissionSeeder.php), so the admin
    | guard is the permission default in practice, with 'web' available
    | for frontend checks.
    |
    | NOTE: written by hand in Phase 2 (not via `artisan vendor:publish`)
    | because no Artisan command was run in this step. Re-publish with
    | `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
    | after `composer install` and diff against this file if in doubt.
    |
    */

    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        'role_pivot_key' => null,
        'permission_pivot_key' => null,
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'team_id',
    ],

    'register_permission_check_method' => true,

    'register_octane_reset_listener' => false,

    'teams' => false,

    'use_passport_client_credentials' => false,

    'display_permission_in_exception' => false,

    'display_role_in_exception' => false,

    'enable_wildcard_permission' => false,

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],
];
