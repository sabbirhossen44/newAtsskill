<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminForgotPasswordRequest;
use App\Repositories\AdminRepository;
use App\Services\AppMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
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
    public function custom_forget_password(AdminForgotPasswordRequest $request): RedirectResponse
    {
        $admin = AdminRepository::findByEmail($request->input('email'));

        if (! $admin) {
            return redirect()->back()->with([
                'messege' => __('Email does not exist'),
                'alert-type' => 'error',
            ]);
        }

        AdminRepository::issueForgetPasswordToken($admin);
        AppMailService::sendUserForgetPassword($admin, 'admin.auth');

        return redirect()->back()->with([
            'messege' => __('A password reset link has been send to your mail'),
            'alert-type' => 'success',
        ]);
    }
}
