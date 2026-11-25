<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StudentLoginRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Student / instructor login (allSolution AuthController shape:
 * validate → find user → auth()->validate() → status check → login →
 * regenerate → redirect()->intended()).
 *
 * Status / role / banned are compared as plain strings (spec §5: no
 * enum casts). The BackedEnum unwrap keeps the checks correct while
 * the model still casts those columns to enums.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(StudentLoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $user = UserRepository::findByEmail($credentials['email']);

        if (! $user || ! auth('web')->validate($credentials)) {
            throw ValidationException::withMessages([
                'email' => __('Invalid credentials please check your email and password'),
            ]);
        }

        $status = $user->status instanceof \BackedEnum ? $user->status->value : $user->status;

        if ($status !== 'active') {
            throw ValidationException::withMessages(['email' => __('Inactive account')]);
        }

        $isBanned = $user->is_banned instanceof \BackedEnum ? $user->is_banned->value : $user->is_banned;

        if ($isBanned === 'yes') {
            return redirect()->back()->with([
                'messege' => __('Your account has been banned'),
                'alert-type' => 'error',
            ]);
        }

        if (! $user->email_verified_at) {
            return redirect()->back()->with([
                'messege' => __('Please verify your email'),
                'alert-type' => 'error',
            ]);
        }

        auth('web')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // The old users table has no last_login_at column, so this is
        // recorded only when the column exists (never breaks login).
        if (Schema::hasColumn('users', 'last_login_at')) {
            $user->forceFill(['last_login_at' => now()])->save();
        }

        $role = $user->role instanceof \BackedEnum ? $user->role->value : $user->role;

        return redirect()->intended(
            $role === 'instructor' ? route('instructor.dashboard') : route('student.dashboard')
        )->with('success', __('Logged in successfully.'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        auth('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', __('Logged out successfully.'));
    }
}
