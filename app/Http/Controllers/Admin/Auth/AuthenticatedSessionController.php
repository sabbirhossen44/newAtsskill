<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Repositories\AdminRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Admin login — allSolution AuthController shape, verbatim (spec §2):
 * validate → find admin → auth('admin')->validate() → status check →
 * login → regenerate → last_login_at → redirect()->intended().
 * DEVIATION (ATS domain): the identity is the separate Admin model +
 * admins table + admin guard, not User + role(ADMIN).
 *
 * Status is compared as a plain string (spec §5: no enum casts). The
 * BackedEnum unwrap keeps the check correct while the model still
 * casts `status` to an enum.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('admin.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $admin = AdminRepository::findByEmail($credentials['email']);

        if (! $admin || ! auth('admin')->validate($credentials)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $status = $admin->status instanceof \BackedEnum ? $admin->status->value : $admin->status;

        if ($status !== 'active') {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'This admin account is inactive.']);
        }

        auth('admin')->login($admin, $request->boolean('remember'));
        $request->session()->regenerate();

        // The old admins table has no last_login_at column, so this is
        // recorded only when the column exists (never breaks login).
        if (Schema::hasColumn('admins', 'last_login_at')) {
            $admin->forceFill(['last_login_at' => now()])->save();
        }

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', 'Welcome back, '.$admin->name.'.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        auth('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
    }
}
