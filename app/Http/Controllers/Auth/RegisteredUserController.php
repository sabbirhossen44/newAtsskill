<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\PaymentGatewayInfo;
use App\Http\Controllers\Concerns\SendsAppMail;
use App\Http\Requests\Auth\RegisterRequest;
use App\Repositories\CourseRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    use SendsAppMail;
    use PaymentGatewayInfo;

    public function create(): View
    {
        $courses = CourseRepository::activeCourses();

        return view('auth.register', compact('courses'));
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = UserRepository::registerByRequest($request->validated());

        $settings = (object) \App\Services\SettingService::all();
        $marketingSettings = (object) [];
        if ($user && $settings->google_tagmanager_status == 'active' && $marketingSettings->register) {
            $register_user = [
                'name' => $user->name,
                'email' => $user->email,
            ];
            session()->put('registerUser', $register_user);
        }

        $this->sendVerifyMailToUserFromTrait('single_user', $user);

        $notification = __('Admission form submitted successfully! A verification link has been sent to your email.');
        $notification = ['messege' => $notification, 'alert-type' => 'success'];

        return redirect()->back()->with($notification);
    }

    public function custom_user_verification($token)
    {
        $user = UserRepository::findByVerificationToken($token);

        if ($user) {
            if ($user->email_verified_at != null) {
                $notification = __('Email already verified');
                $notification = ['messege' => $notification, 'alert-type' => 'error'];

                return redirect()->route('login')->with($notification);
            }

            UserRepository::markEmailVerified($user);

            $notification = __('Verification successful please try to login now');
            $notification = ['messege' => $notification, 'alert-type' => 'success'];

            return redirect()->route('login')->with($notification);
        }

        $notification = __('Invalid token');
        $notification = ['messege' => $notification, 'alert-type' => 'error'];

        return redirect()->route('register')->with($notification);
    }
}
