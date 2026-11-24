<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Replacement for the old global helpers checkAdminHasPermission() /
 * checkAdminHasPermissionAndThrowException() (old app/Helpers/helper.php,
 * not present in the new helpers.php). Behaviour is the same: an admin
 * without the permission gets a 403 (old code threw
 * AccessPermissionDeniedException, which the scaffold does not provide
 * yet; abort(403) is the equivalent HTTP outcome).
 */
trait ChecksAdminPermissions
{
    protected function checkAdminPermission(string $permission): void
    {
        $admin = Auth::guard('admin')->user();
        if (! $admin || ! $admin->can($permission)) {
            abort(403);
        }
    }
}
