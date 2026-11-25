<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Verbatim allSolution shape: admin.php is loaded under the
            // 'web' middleware with the 'admin' prefix applied here
            // (routes/admin.php itself only adds the 'admin.' name).
            Route::middleware('web')->prefix('admin')->group(base_path('routes/admin.php'));
            // allSolution has no auth.php; the build's student auth routes
            // must keep working, so they are loaded here in the web group
            // exactly as before.
            Route::middleware('web')->group(base_path('routes/auth.php'));
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
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The file is too large to upload. Please choose a smaller file and try again.',
                ], 413);
            }
        });
    })->create();
