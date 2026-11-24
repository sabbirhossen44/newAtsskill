<?php

use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Sanctum
    |--------------------------------------------------------------------------
    |
    | Stock Sanctum v4 configuration. API tokens are stored in the
    | `personal_access_tokens` table (Phase 1 migration
    | 2026_11_24_000151). App\Models\User and App\Models\Admin both use
    | HasApiTokens (Sanctum).
    |
    | NOTE: written by hand in Phase 2 (not via `artisan vendor:publish`)
    | because no Artisan command was run in this step. Re-publish with
    | `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
    | after `composer install` and diff against this file if in doubt.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort(),
    ))),

    'guard' => ['web'],

    'expiration' => null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
