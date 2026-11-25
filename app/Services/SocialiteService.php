<?php

namespace App\Services;

use App\Repositories\SocialiteCredentialRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Social login (allSolution style: config + user creation live in a
 * service, not in controller traits). Provider config comes from the
 * settings table, exactly as the old SetConfigTrait did; a new social
 * user is created with plain string status/banned values.
 */
class SocialiteService
{
    public static function configureDriver(?string $driver): void
    {
        if ($driver === 'google') {
            Config::set('services.google.client_id', setting('gmail_client_id'));
            Config::set('services.google.client_secret', setting('gmail_secret_id'));
            Config::set('services.google.redirect', url('/auth/google/callback'));
        } elseif ($driver === 'facebook') {
            Config::set('services.facebook.client_id', setting('facebook_app_id'));
            Config::set('services.facebook.client_secret', setting('facebook_app_secret'));
            Config::set('services.facebook.redirect', setting('facebook_redirect_url'));
        } elseif ($driver === 'twitter') {
            Config::set('services.twitter.client_id', setting('twitter_client_id'));
            Config::set('services.twitter.client_secret', setting('twitter_secret_id'));
            Config::set('services.twitter.redirect', setting('twitter_redirect_url'));
        } elseif ($driver === 'linkedin') {
            Config::set('services.linkedin.client_id', setting('linkedin_client_id'));
            Config::set('services.linkedin.client_secret', setting('linkedin_secret_id'));
            Config::set('services.linkedin.redirect', setting('linkedin_redirect_url'));
        }
    }

    /**
     * Create the user for a first-time social login (or reuse the
     * given one) and store the provider credential. Returns the stored
     * SocialiteCredential, or null when creation failed.
     */
    public static function createSocialUser($callbackUser, string $providerName, $user)
    {
        if (! $user) {
            $password = Str::random(10);

            $user = UserRepository::createSocialUser([
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
                AppMailService::sendSocialLoginDefaultPassword($user, $password);
            } catch (Exception $e) {
                session(['error' => $e->getMessage()]);

                if (app()->isLocal()) {
                    Log::error($e);
                }
            }
        }

        return SocialiteCredentialRepository::storeByRequest($user->id, [
            'provider_name' => $providerName,
            'provider_id' => $callbackUser->getId(),
            'access_token' => $callbackUser->token ?? null,
            'refresh_token' => $callbackUser->refreshToken ?? null,
        ]);
    }
}
