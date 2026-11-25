<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPasswordUpdateRequest;
use App\Http\Requests\Admin\AdminProfileUpdateRequest;
use App\Repositories\AdminRepository;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function edit_profile()
    {
        abort_unless(auth('admin')->user()?->can('admin.profile.view'), 403);
        $admin = Auth::guard('admin')->user();

        return view('admin.profile.edit_profile', compact('admin'));
    }

    public function profile_update(AdminProfileUpdateRequest $request)
    {
        $this->checkAdminPermission('admin.profile.update');

        $admin = Auth::guard('admin')->user();

        $data = array_merge($request->validated(), $request->only('bio'));

        AdminRepository::updateProfile($admin, $data, $request->file('image'));

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value);
    }

    public function update_password(AdminPasswordUpdateRequest $request)
    {
        $this->checkAdminPermission('admin.profile.update');

        $admin = Auth::guard('admin')->user();

        if (AdminRepository::changePassword($admin, $request->input('current_password'), $request->input('password'))) {
            $notification = __('Password updated successfully');
            $notification = ['messege' => $notification, 'alert-type' => 'success'];

            return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, '', [], $notification);
        }

        $notification = __('Current password does not match');
        $notification = ['messege' => $notification, 'alert-type' => 'error'];

        return redirect()->back()->with($notification);
    }
}
