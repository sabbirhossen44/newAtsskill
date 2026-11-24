<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
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
            $user = User::create([
                'name' => $callbackUser->name,
                'email' => $callbackUser->email,
                'status' => 'active',
                'is_banned' => 'no',
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

        $socialite = $user->socialite()->create([
            'provider_name' => $provider_name,
            'provider_id' => $callbackUser->getId(),
            'access_token' => $callbackUser->token ?? null,
            'refresh_token' => $callbackUser->refreshToken ?? null,
        ]);

        return $socialite;
    }
}
