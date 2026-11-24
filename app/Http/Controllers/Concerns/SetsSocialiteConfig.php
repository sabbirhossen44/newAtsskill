<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Config;

/** Port of the old App\Traits\SetConfigTrait (settings come from setting()). */
trait SetsSocialiteConfig
{
    protected static function setGoogleLoginInfo()
    {
        Config::set('services.google.client_id', setting('gmail_client_id'));
        Config::set('services.google.client_secret', setting('gmail_secret_id'));
        Config::set('services.google.redirect', url('/auth/google/callback'));
    }

    protected static function setFacebookLoginInfo()
    {
        Config::set('services.facebook.client_id', setting('facebook_app_id'));
        Config::set('services.facebook.client_secret', setting('facebook_app_secret'));
        Config::set('services.facebook.redirect', setting('facebook_redirect_url'));
    }

    protected static function setTwitterLoginInfo()
    {
        Config::set('services.twitter.client_id', setting('twitter_client_id'));
        Config::set('services.twitter.client_secret', setting('twitter_secret_id'));
        Config::set('services.twitter.redirect', setting('twitter_redirect_url'));
    }

    protected static function setLinkedinLoginInfo()
    {
        Config::set('services.linkedin.client_id', setting('linkedin_client_id'));
        Config::set('services.linkedin.client_secret', setting('linkedin_secret_id'));
        Config::set('services.linkedin.redirect', setting('linkedin_redirect_url'));
    }
}
