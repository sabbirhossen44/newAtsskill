<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Alias kept for the new route naming (old method is dashboard()). */
    public function index(Request $request)
    {
        return $this->dashboard($request);
    }

    public function dashboard(Request $request)
    {
        // remove intended url from session
        $request->session()->forget('url');

        $data = AdminDashboardService::dashboardData($request);

        return view('admin.dashboard', compact('data'));
    }

    public function setLanguage()
    {
        AdminDashboardService::setLanguage(request('code'));

        $notification = __('Language Changed Successfully');
        $notification = ['messege' => $notification, 'alert-type' => 'success'];

        return redirect()->back()->with($notification);
    }
}
