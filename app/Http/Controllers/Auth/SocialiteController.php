<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\SocialiteCredentialRepository;
use App\Repositories\UserRepository;
use App\Services\SocialiteService;
use Illuminate\Http\RedirectResponse;

/**
 * Social login (allSolution style: thin controller, config + user
 * creation in SocialiteService). Status / banned are compared as
 * plain strings (spec §5: no enum casts); the BackedEnum unwrap keeps
 * the checks correct while the model still casts those columns.
 */
class SocialiteController extends Controller
{
    public function redirectToDriver($driver): RedirectResponse
    {
        if (! in_array($driver, ['facebook', 'google'], true)) {
            return redirect()->back()->with([
                'messege' => __('Invalid Social Login Type!'),
                'alert-type' => 'error',
            ]);
        }

        SocialiteService::configureDriver($driver);

        return \Laravel\Socialite\Facades\Socialite::driver($driver)->redirect();
    }

    public function handleDriverCallback($driver): RedirectResponse
    {
        if (! in_array($driver, ['facebook', 'google'], true)) {
            return redirect()->back()->with([
                'messege' => __('Invalid Social Login Type!'),
                'alert-type' => 'error',
            ]);
        }

        try {
            SocialiteService::configureDriver($driver);

            $callbackUser = \Laravel\Socialite\Facades\Socialite::driver($driver)->stateless()->user();
            $user = UserRepository::findByEmail($callbackUser->getEmail());

            if ($user) {
                $status = $user->status instanceof \BackedEnum ? $user->status->value : $user->status;
                $isBanned = $user->is_banned instanceof \BackedEnum ? $user->is_banned->value : $user->is_banned;

                if ($status !== 'active' || $isBanned !== 'no') {
                    return redirect()->back()->with([
                        'messege' => __('Inactive account'),
                        'alert-type' => 'error',
                    ]);
                }

                if (app()->isProduction() && $user->email_verified_at === null) {
                    return redirect()->back()->with([
                        'messege' => __('Please verify your email'),
                        'alert-type' => 'error',
                    ]);
                }

                $findDriver = SocialiteCredentialRepository::findForUser($user->id, $driver, $callbackUser->getId());

                if (! $findDriver) {
                    $socialite = SocialiteService::createSocialUser($callbackUser, $driver, $user);

                    if (! $socialite) {
                        return redirect()->back()->with([
                            'messege' => __('Login Failed'),
                            'alert-type' => 'error',
                        ]);
                    }
                }

                auth('web')->login($user, true);

                return redirect()->intended(route('student.dashboard'))->with([
                    'messege' => __('Logged in successfully.'),
                    'alert-type' => 'success',
                ]);
            }

            $socialite = SocialiteService::createSocialUser($callbackUser, $driver, false);

            if (! $socialite) {
                return redirect()->back()->with([
                    'messege' => __('Login Failed'),
                    'alert-type' => 'error',
                ]);
            }

            $user = UserRepository::findById($socialite->user_id);
            auth('web')->login($user, true);

            return redirect()->intended(route('student.dashboard'))->with([
                'messege' => __('Logged in successfully.'),
                'alert-type' => 'success',
            ]);
        } catch (\Exception $e) {
            return to_route('login');
        }
    }
}
