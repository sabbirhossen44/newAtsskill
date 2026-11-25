<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Repositories\CourseRepository;
use App\Repositories\UserRepository;
use App\Services\AppMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $courses = CourseRepository::activeCourses();

        return view('auth.register', compact('courses'));
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = UserRepository::registerByRequest($request->validated());

        if ($user && setting('google_tagmanager_status') === 'active') {
            session()->put('registerUser', [
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        AppMailService::sendVerifyMailToUser('single_user', $user);

        return redirect()->back()->with([
            'messege' => __('Admission form submitted successfully! A verification link has been sent to your email.'),
            'alert-type' => 'success',
        ]);
    }

    public function custom_user_verification($token): RedirectResponse
    {
        $user = UserRepository::findByVerificationToken($token);

        if (! $user) {
            return redirect()->route('register')->with([
                'messege' => __('Invalid token'),
                'alert-type' => 'error',
            ]);
        }

        if ($user->email_verified_at !== null) {
            return redirect()->route('login')->with([
                'messege' => __('Email already verified'),
                'alert-type' => 'error',
            ]);
        }

        UserRepository::markEmailVerified($user);

        return redirect()->route('login')->with([
            'messege' => __('Verification successful please try to login now'),
            'alert-type' => 'success',
        ]);
    }
}
