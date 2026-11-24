<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes — allSolution style
|--------------------------------------------------------------------------
| One Route::controller() group per controller. URIs, methods, names
| and middleware are unchanged from the old (Breeze-based) code.
*/

Route::middleware('guest:web')->group(function () {
    // Registration
    Route::controller(RegisteredUserController::class)->group(function () {
        Route::get('register', 'create')->name('register');
        Route::post('register', 'store');
        Route::get('user-verification/{token}', 'custom_user_verification')->name('user-verification');
    });

    // Login
    Route::controller(AuthenticatedSessionController::class)->group(function () {
        Route::get('login', 'create')->name('login');
        Route::post('user-login', 'store')->name('user-login');
    });

    // Password reset
    Route::controller(PasswordResetLinkController::class)->group(function () {
        Route::get('forgot-password', 'create')->name('password.request');
        Route::post('forgot-password', 'store')->name('password.email');
        Route::post('forget-password', 'custom_forget_password')->name('forget-password');
    });

    Route::controller(NewPasswordController::class)->group(function () {
        Route::get('reset-password/{token}', 'create')->name('password.reset');
        Route::post('reset-password', 'store')->name('password.store');
        Route::get('reset-password-page/{token}', 'custom_reset_password_page')->name('reset-password-page');
        Route::post('reset-password-store/{token}', 'custom_reset_password_store')->name('reset-password-store');
    });

    // Social login
    Route::controller(SocialiteController::class)->group(function () {
        Route::get('auth/{driver}', 'redirectToDriver')->name('auth.social');
        Route::get('auth/{driver}/callback', 'handleDriverCallback')->name('auth.social.callback');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Confirm password
    Route::controller(ConfirmablePasswordController::class)->group(function () {
        Route::get('confirm-password', 'show')->name('password.confirm');
        Route::post('confirm-password', 'store');
    });

    Route::controller(PasswordController::class)->group(function () {
        Route::put('password', 'update')->name('password.update');
    });

    Route::controller(AuthenticatedSessionController::class)->group(function () {
        Route::post('logout', 'destroy')->name('logout');
    });
});
