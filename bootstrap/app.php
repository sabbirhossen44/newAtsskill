<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // admin.php / auth.php are loaded here (not via the stock
            // web/api files), so give them the 'web' middleware group
            // explicitly — otherwise session/errors/CSRF do not run.
            \Illuminate\Support\Facades\Route::middleware('web')->group(function () {
                require __DIR__.'/../routes/admin.php';
                require __DIR__.'/../routes/auth.php';
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Web group: locale + XSS sanitisation, as in the old Kernel.php.
        $middleware->web(append: [
            \App\Http\Middleware\SetLocaleMiddleware::class,
            \App\Http\Middleware\XssSanitization::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\Role::class,
            'approved.instructor' => \App\Http\Middleware\ApprovedInstructorMiddleware::class,
            'maintenance.mode' => \App\Http\Middleware\MaintenanceModeMiddleware::class,
            'translation' => \App\Http\Middleware\SetLocaleMiddleware::class,
            'set.locale' => \App\Http\Middleware\SetLocaleMiddleware::class,
            // No dedicated AdminPermission class exists; admin permission
            // checks run in EnsureAdminAuthenticated + ChecksAdminPermissions.
            'admin.permission' => \App\Http\Middleware\EnsureAdminAuthenticated::class,
            'admin.auth' => \App\Http\Middleware\EnsureAdminAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
