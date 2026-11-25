<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\UserStatusEnum;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin guard gate for the admin area: the admin must be authenticated
 * via the `admin` guard and have an active status. (Old code relied on
 * the stock auth:admin middleware plus per-controller status checks in
 * the admin login controller; this middleware makes the active-status
 * requirement explicit on every protected admin route.)
 */
class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $admin = Auth::guard('admin')->user();

        if ($admin && $admin->status !== UserStatusEnum::ACTIVE) {
            Auth::guard('admin')->logout();

            return redirect()->route('admin.login')->with([
                'messege' => __('Inactive account'),
                'alert-type' => 'error',
            ]);
        }

        return $next($request);
    }
}
