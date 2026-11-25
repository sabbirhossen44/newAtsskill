<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\PaymentGatewayInfo;
use App\Http\Controllers\Concerns\SendsAppMail;
use App\Http\Requests\Auth\CustomForgotPasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    use SendsAppMail;
    use PaymentGatewayInfo;

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
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    public function custom_forget_password(CustomForgotPasswordRequest $request)
    {
        $user = UserRepository::findByEmail($request->email);

        if ($user) {
            UserRepository::setForgetPasswordToken($user);

            $this->sendUserForgetPasswordFromTrait($user);

            $notification = __('A password reset link has been send to your mail');
            $notification = ['messege' => $notification, 'alert-type' => 'success'];

            return redirect()->back()->with($notification);
        }

        $notification = __('Email does not exist');
        throw ValidationException::withMessages(['email' => $notification]);
    }
}
