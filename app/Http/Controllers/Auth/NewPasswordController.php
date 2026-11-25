<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CustomResetPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    public function custom_reset_password_page(Request $request, $token): View|RedirectResponse
    {
        $user = UserRepository::findByForgetPasswordToken($token);

        if (! $user) {
            return redirect()->route('password.request')->with([
                'messege' => __('Invalid token, please try again'),
                'alert-type' => 'error',
            ]);
        }

        return view('auth.reset-password', ['user' => $user, 'token' => $token]);
    }

    public function custom_reset_password_store(CustomResetPasswordRequest $request, $token): RedirectResponse
    {
        $user = UserRepository::findByForgetPasswordTokenAndEmail($token, $request->email);

        if (! $user) {
            return redirect()->back()->with([
                'messege' => __('Invalid token, please try again'),
                'alert-type' => 'error',
            ]);
        }

        UserRepository::resetCustomPassword($user, $request->password);

        return redirect()->route('login')->with([
            'messege' => __('Password Reset successfully'),
            'alert-type' => 'success',
        ]);
    }
}
