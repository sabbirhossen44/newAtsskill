<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\UserBannedEnum;
use App\Enums\UserStatusEnum;
use App\Repositories\SocialiteCredentialRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/** Port of the old App\Traits\NewUserCreateTrait. */
trait CreatesSocialUser
{
    use SendsAppMail;

    private function createNewUser($callbackUser, $provider_name, $user)
    {
        if (! $user) {
            $password = Str::random(10);
            $user = UserRepository::createSocialUser([
                'name' => $callbackUser->name,
                'email' => $callbackUser->email,
                'status' => UserStatusEnum::ACTIVE->value,
                'is_banned' => UserBannedEnum::NO->value,
                'image' => $callbackUser->getAvatar(),
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'verification_token' => Str::random(100),
            ]);

            if ($user && setting('google_tagmanager_status') === 'active') {
                session()->put('registerUser', ['name' => $user->name, 'email' => $user->email]);
            }
            try {
                $this->sendSocialLoginDefaultPasswordFromTrait($user, $password);
            } catch (Exception $e) {
                session(['error' => $e->getMessage()]);
                if (app()->isLocal()) {
                    Log::error($e);
                }
            }
        }

        return SocialiteCredentialRepository::storeByRequest($user->id, [
            'provider_name' => $provider_name,
            'provider_id' => $callbackUser->getId(),
            'access_token' => $callbackUser->token ?? null,
            'refresh_token' => $callbackUser->refreshToken ?? null,
        ]);
    }
}
