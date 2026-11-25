<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Concerns\SendsAppMail;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminForgotPasswordRequest;
use App\Repositories\AdminRepository;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    use SendsAppMail;

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('admin.auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function custom_forget_password(AdminForgotPasswordRequest $request)
    {
        $admin = AdminRepository::findByEmail($request->input('email'));

        if ($admin) {
            AdminRepository::issueForgetPasswordToken($admin);

            $this->sendUserForgetPasswordFromTrait($admin, 'admin.auth');

            $notification = __('A password reset link has been send to your mail');
            $notification = ['messege' => $notification, 'alert-type' => 'success'];

            return redirect()->back()->with($notification);
        }

        $notification = __('Email does not exist');
        $notification = ['messege' => $notification, 'alert-type' => 'error'];

        return redirect()->back()->with($notification);
    }
}
