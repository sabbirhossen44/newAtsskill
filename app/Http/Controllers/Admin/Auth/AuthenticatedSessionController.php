<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Repositories\AdminRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

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
     *
     * Auth logic is unchanged from the old code: admin guard, active
     * status check, Hash check, then guard attempt, redirect to the
     * admin dashboard. Only the validation moved to a FormRequest
     * and the admin lookup moved to AdminRepository.
     */
    public function store(AdminLoginRequest $request)
    {
        $credential = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $admin = AdminRepository::findByEmail($request->email);

        if ($admin) {
            if ($admin->status === UserStatusEnum::ACTIVE) {
                if (Hash::check($request->password, $admin->password)) {
                    if (Auth::guard('admin')->attempt($credential, $request->remember)) {
                        $notification = __('Logged in successfully.');
                        $notification = ['messege' => $notification, 'alert-type' => 'success'];

                        return redirect()->route('admin.dashboard')->with($notification);
                    }
                } else {
                    $notification = __('Invalid Password');
                    $notification = ['messege' => $notification, 'alert-type' => 'error'];

                    return redirect()->back()->with($notification);
                }
            } else {
                $notification = __('Inactive account');
                $notification = ['messege' => $notification, 'alert-type' => 'error'];

                return redirect()->back()->with($notification);
            }
        } else {
            $notification = __('Invalid Email');
            $notification = ['messege' => $notification, 'alert-type' => 'error'];

            return redirect()->back()->with($notification);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $notification = __('Logged out successfully.');
        $notification = ['messege' => $notification, 'alert-type' => 'success'];

        return redirect()->route('admin.login')->with($notification);
    }
}
