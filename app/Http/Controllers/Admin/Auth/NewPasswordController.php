<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResetPasswordRequest;
use App\Repositories\AdminRepository;
use Illuminate\Http\Request;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function custom_reset_password_page(Request $request, $token)
    {
        $admin = AdminRepository::findByForgetPasswordToken($token);

        if (! $admin) {
            $notification = __('Invalid token, please try again');
            $notification = ['messege' => $notification, 'alert-type' => 'error'];

            return redirect()->route('password.request')->with($notification);
        }

        return view('admin.auth.reset-password', ['admin' => $admin, 'token' => $token]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function custom_reset_password_store(AdminResetPasswordRequest $request, $token)
    {
        $admin = AdminRepository::findByForgetPasswordToken($token, $request->input('email'));

        if (! $admin) {
            $notification = __('Invalid token, please try again');
            $notification = ['messege' => $notification, 'alert-type' => 'error'];

            return redirect()->back()->with($notification);
        }

        AdminRepository::resetPassword($admin, $request->input('password'));

        $notification = __('Password Reset successfully');
        $notification = ['messege' => $notification, 'alert-type' => 'success'];

        return redirect()->route('admin.login')->with($notification);
    }
}
