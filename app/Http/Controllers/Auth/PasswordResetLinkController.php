<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CustomForgotPasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Repositories\UserRepository;
use App\Services\AppMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    public function custom_forget_password(CustomForgotPasswordRequest $request): RedirectResponse
    {
        $user = UserRepository::findByEmail($request->email);

        if (! $user) {
            throw ValidationException::withMessages(['email' => __('Email does not exist')]);
        }

        UserRepository::setForgetPasswordToken($user);
        AppMailService::sendUserForgetPassword($user);

        return redirect()->back()->with([
            'messege' => __('A password reset link has been send to your mail'),
            'alert-type' => 'success',
        ]);
    }
}
