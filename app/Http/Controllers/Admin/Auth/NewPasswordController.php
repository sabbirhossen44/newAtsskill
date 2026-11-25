<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResetPasswordRequest;
use App\Repositories\AdminRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function custom_reset_password_page(Request $request, $token): View|RedirectResponse
    {
        $admin = AdminRepository::findByForgetPasswordToken($token);

        if (! $admin) {
            return redirect()->route('password.request')->with([
                'messege' => __('Invalid token, please try again'),
                'alert-type' => 'error',
            ]);
        }

        return view('admin.auth.reset-password', ['admin' => $admin, 'token' => $token]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function custom_reset_password_store(AdminResetPasswordRequest $request, $token): RedirectResponse
    {
        $admin = AdminRepository::findByForgetPasswordToken($token, $request->input('email'));

        if (! $admin) {
            return redirect()->back()->with([
                'messege' => __('Invalid token, please try again'),
                'alert-type' => 'error',
            ]);
        }

        AdminRepository::resetPassword($admin, $request->input('password'));

        return redirect()->route('admin.login')->with([
            'messege' => __('Password Reset successfully'),
            'alert-type' => 'success',
        ]);
    }
}
