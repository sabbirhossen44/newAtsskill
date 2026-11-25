<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Per-page size for admin listings, allSolution style (default 15,
     * whitelist [10, 25, 50, 100]). allSolution defines this per
     * controller; here it lives on the base controller so every admin
     * controller shares the one implementation.
     */
    protected function perPage(Request $request, int $default = 15): int
    {
        $perPage = (int) $request->input('per_page', $default);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : $default;
    }
}
