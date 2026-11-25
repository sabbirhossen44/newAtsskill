<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPasswordUpdateRequest;
use App\Http\Requests\Admin\AdminProfileUpdateRequest;
use App\Repositories\AdminRepository;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit_profile()
    {
        abort_unless(auth('admin')->user()?->can('admin.profile.view'), 403);
        $admin = Auth::guard('admin')->user();

        return view('admin.profile.edit_profile', compact('admin'));
    }

    public function profile_update(AdminProfileUpdateRequest $request)
    {
        $admin = Auth::guard('admin')->user();

        $data = array_merge($request->validated(), $request->only('bio'));

        AdminRepository::updateProfile($admin, $data, $request->file('image'));

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function update_password(AdminPasswordUpdateRequest $request)
    {
        $admin = Auth::guard('admin')->user();

        if (AdminRepository::changePassword($admin, $request->input('current_password'), $request->input('password'))) {
            return redirect()->back()->with('success', 'Password updated successfully.');
        }

        return redirect()->back()->with('error', 'Current password does not match.');
    }
}
