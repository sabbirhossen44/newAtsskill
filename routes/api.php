<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — allSolution style
|--------------------------------------------------------------------------
| Grouped by area (public / authenticated) with one
| Route::controller() group per controller, as in allSolution
| routes/api.php. The old code had no real API — only this stub
| route, which is kept exactly as it was.
*/

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
