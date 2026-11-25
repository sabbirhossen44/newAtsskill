<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SocialiteDriverEnum;
use App\Enums\UserBannedEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Repositories\SocialiteCredentialRepository;
use App\Repositories\UserRepository;
use App\Http\Controllers\Concerns\CreatesSocialUser;
use App\Http\Controllers\Concerns\SetsSocialiteConfig;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    use CreatesSocialUser, SetsSocialiteConfig;

    public function __construct()
    {
        $driver = request('driver', null);
        if ($driver == SocialiteDriverEnum::FACEBOOK->value) {
            self::setFacebookLoginInfo();
        } elseif ($driver == SocialiteDriverEnum::GOOGLE->value) {
            self::setGoogleLoginInfo();
        }
    }

    public function redirectToDriver($driver)
    {
        if (in_array($driver, SocialiteDriverEnum::getAll())) {
            return Socialite::driver($driver)->redirect();
        }
        $notification = __('Invalid Social Login Type!');
        $notification = ['messege' => $notification, 'alert-type' => 'error'];

        return redirect()->back()->with($notification);
    }

    public function handleDriverCallback($driver)
    {
        if (! in_array($driver, SocialiteDriverEnum::getAll())) {
            $notification = __('Invalid Social Login Type!');
            $notification = ['messege' => $notification, 'alert-type' => 'error'];

            return redirect()->back()->with($notification);
        }
        try {
            $provider_name = SocialiteDriverEnum::from($driver)->value;
            $callbackUser = Socialite::driver($provider_name)->stateless()->user();
            $user = UserRepository::findByEmail($callbackUser->getEmail());
            if ($user) {
                $findDriver = SocialiteCredentialRepository::findForUser($user->id, $provider_name, $callbackUser->getId());

                if ($findDriver) {
                    if ($user->status === UserStatusEnum::ACTIVE) {
                        if ($user->is_banned === UserBannedEnum::NO) {
                            if (app()->isProduction() && $user->email_verified_at == null) {
                                $notification = __('Please verify your email');
                                $notification = ['messege' => $notification, 'alert-type' => 'error'];

                                return redirect()
                                    ->back()
                                    ->with($notification);
                            }
                            if ($findDriver) {
                                Auth::guard('web')->login($user, true);
                                $notification = __('Logged in successfully.');
                                $notification = ['messege' => $notification, 'alert-type' => 'success'];

                                return redirect()
                                    ->intended(route('student.dashboard'))
                                    ->with($notification);
                            }
                        } else {
                            $notification = __('Inactive account');
                            $notification = ['messege' => $notification, 'alert-type' => 'error'];

                            return redirect()
                                ->back()
                                ->with($notification);
                        }
                    } else {
                        $notification = __('Inactive account');
                        $notification = ['messege' => $notification, 'alert-type' => 'error'];

                        return redirect()
                            ->back()
                            ->with($notification);
                    }
                } else {
                    $socialite = $this->createNewUser(callbackUser: $callbackUser, provider_name: $provider_name, user: $user);

                    if ($socialite) {
                        Auth::guard('web')->login($user, true);
                        $notification = __('Logged in successfully.');
                        $notification = ['messege' => $notification, 'alert-type' => 'success'];

                        return redirect()
                            ->intended(route('user.dashboard'))
                            ->with($notification);
                    }

                    $notification = __('Login Failed');
                    $notification = ['messege' => $notification, 'alert-type' => 'error'];

                    return redirect()
                        ->back()
                        ->with($notification);
                }
            } else {
                if ($callbackUser) {
                    $socialite = $this->createNewUser(callbackUser: $callbackUser, provider_name: $provider_name, user: false);
                    if ($socialite) {
                        $user = UserRepository::findById($socialite->user_id);
                        Auth::guard('web')->login($user, true);
                        $notification = __('Logged in successfully.');
                        $notification = ['messege' => $notification, 'alert-type' => 'success'];

                        return redirect()
                            ->intended(route('student.dashboard'))
                            ->with($notification);
                    }

                    $notification = __('Login Failed');
                    $notification = ['messege' => $notification, 'alert-type' => 'error'];

                    return redirect()
                        ->back()
                        ->with($notification);
                }

                $notification = __('Login Failed');
                $notification = ['messege' => $notification, 'alert-type' => 'error'];

                return redirect()->back()->with($notification);
            }
        } catch (\Exception $e) {
            return to_route('login');
        }
    }
}
