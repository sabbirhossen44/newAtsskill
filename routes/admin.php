<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Global\CloudStorageController;
use App\Http\Middleware\EnsureAdminAuthenticated;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes — allSolution style
|--------------------------------------------------------------------------
| One Route::controller() group per resource inside a single
| prefix('admin')->name('admin.') wrapper. Explicit routes only —
| no Route::resource (see plan/allsolution-style.md §4).
| URIs, methods, names and middleware are unchanged from the old code.
*/

Route::prefix('admin')->name('admin.')->group(function () {
    // Guest (admin login / password reset) — no guest middleware here,
    // exactly as in the old code (allSolution wraps these in guest:admin,
    // but the old routes had no such middleware, so it is not added).
    Route::controller(AuthenticatedSessionController::class)->group(function () {
        Route::get('login', 'create')->name('login');
        Route::post('store-login', 'store')->name('store-login');
        Route::post('logout', 'destroy')->name('logout');
    });

    Route::controller(PasswordResetLinkController::class)->group(function () {
        Route::get('forgot-password', 'create')->name('password.request');
        Route::post('forget-password', 'custom_forget_password')->name('forget-password');
    });

    Route::controller(NewPasswordController::class)->group(function () {
        Route::get('reset-password/{token}', 'custom_reset_password_page')->name('password.reset');
        Route::post('reset-password-store/{token}', 'custom_reset_password_store')->name('password.reset-store');
    });

    // Authenticated admin area
    Route::middleware(['auth:admin', EnsureAdminAuthenticated::class])->group(function () {
        Route::get('/', [DashboardController::class, 'dashboard']);
        Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        // Admin profile
        Route::controller(AdminProfileController::class)->group(function () {
            Route::get('edit-profile', 'edit_profile')->name('edit-profile');
            Route::put('profile-update', 'profile_update')->name('profile-update');
            Route::put('update-password', 'update_password')->name('update-password');
        });

        // Roles — assign routes first: 'assign' must win over {role}
        Route::controller(RolesController::class)->group(function () {
            Route::get('role/assign', 'assignRoleView')->name('role.assign');
            Route::post('role/assign/{id}', 'getAdminRoles')->name('role.assign.admin');
            Route::put('role/assign', 'assignRoleUpdate')->name('role.assign.update');
            Route::get('role', 'index')->name('role.index');
            Route::get('role/create', 'create')->name('role.create');
            Route::post('role', 'store')->name('role.store');
            Route::get('role/{role}', 'show')->name('role.show');
            Route::get('role/{role}/edit', 'edit')->name('role.edit');
            Route::match(['put', 'patch'], 'role/{role}', 'update')->name('role.update');
            Route::delete('role/{role}', 'destroy')->name('role.destroy');
        });

        // Team members
        Route::controller(TeamMemberController::class)->group(function () {
            Route::get('team-members', 'index')->name('team-members.index');
            Route::get('team-members/create', 'create')->name('team-members.create');
            Route::post('team-members', 'store')->name('team-members.store');
            Route::get('team-members/{team_member}', 'show')->name('team-members.show');
            Route::get('team-members/{team_member}/edit', 'edit')->name('team-members.edit');
            Route::match(['put', 'patch'], 'team-members/{team_member}', 'update')->name('team-members.update');
            Route::delete('team-members/{team_member}', 'destroy')->name('team-members.destroy');
            Route::put('team-members/status-update/{id}', 'statusUpdate')->name('team-members.status-update');
        });

        // Instructors
        Route::controller(InstructorController::class)->group(function () {
            Route::get('instructors', 'index')->name('instructors.index');
            Route::get('instructors/create', 'create')->name('instructors.create');
            Route::post('instructors', 'store')->name('instructors.store');
            Route::get('instructors/{instructor}', 'show')->name('instructors.show');
            Route::get('instructors/{instructor}/edit', 'edit')->name('instructors.edit');
            Route::match(['put', 'patch'], 'instructors/{instructor}', 'update')->name('instructors.update');
            Route::delete('instructors/{instructor}', 'destroy')->name('instructors.destroy');
            Route::put('instructors/status-update/{id}', 'statusUpdate')->name('instructors.status-update');
        });

        // Admins (no show route, as in the old code)
        Route::controller(AdminController::class)->group(function () {
            Route::get('admin', 'index')->name('admin.index');
            Route::get('admin/create', 'create')->name('admin.create');
            Route::post('admin', 'store')->name('admin.store');
            Route::get('admin/{admin}/edit', 'edit')->name('admin.edit');
            Route::match(['put', 'patch'], 'admin/{admin}', 'update')->name('admin.update');
            Route::delete('admin/{admin}', 'destroy')->name('admin.destroy');
            Route::put('admin-status/{id}', 'changeStatus')->name('admin.status');
        });

        // Settings
        Route::controller(SettingController::class)->group(function () {
            Route::get('settings', 'settings')->name('settings');
        });

        // Cloud storage
        Route::controller(CloudStorageController::class)->group(function () {
            Route::post('cloud/store', 'store')->name('cloud.store');
        });
    });
});
