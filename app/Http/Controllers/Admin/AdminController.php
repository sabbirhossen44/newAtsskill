<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;

class AdminController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index()
    {
        $this->checkAdminPermission('admin.view');

        $admins = Admin::paginate(15);

        return view('admin.admin-list.index')->with([
            'admins' => $admins,
        ]);

    }

    public function create()
    {
        $this->checkAdminPermission('admin.create');
        $roles = Role::all();

        return view('admin.admin-list.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->checkAdminPermission('admin.store');
        $rules = [
            'name' => 'required',
            'email' => 'required|unique:admins',
            'password' => 'required|min:4',
            'status' => 'required',
            'role' => 'nullable|array',
        ];
        $customMessages = [
            'name.required' => __('Name is required'),
            'email.required' => __('Email is required'),
            'status.required' => __('Status is required'),
            'email.unique' => __('Email already exist'),
            'password.required' => __('Password is required'),
            'password.min' => __('Password Must be 4 characters'),
            'role.array' => __('You must select role'),
        ];
        $this->validate($request, $rules, $customMessages);

        $admin = new Admin();
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->status = $request->status;
        $admin->password = Hash::make($request->password);
        $admin->save();
        if ($request->role) {
            $admin->syncRoles($request->role);
        }

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.admin.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('admin.edit');
        $admin = Admin::findOrFail($id);
        $roles = Role::all();

        return view('admin.admin-list.edit', compact('roles', 'admin'));
    }

    public function update(Request $request, $id)
    {
        $this->checkAdminPermission('admin.update');
        $admin = Admin::find($id);
        abort_if($admin->id == 1, 403);
        $rules = [
            'name' => 'required',
            'email' => 'required|unique:admins,email,'.$admin->id,
            'password' => 'nullable|min:4',
            'status' => 'required',
            'role' => 'nullable|array',
        ];
        $customMessages = [
            'name.required' => __('Name is required'),
            'email.required' => __('Email is required'),
            'email.unique' => __('Email already exist'),
            'password.min' => __('Password Must be 4 characters'),
            'role.array' => __('You must select role'),
        ];
        $this->validate($request, $rules, $customMessages);

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->status = $request->status;
        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();
        if ($request->role) {
            $admin->syncRoles($request->role);
        }

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.admin.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('admin.delete');
        $admin = Admin::findOrFail($id);
        abort_if($admin->id == 1, 403);
        $admin->delete();

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.admin.index');
    }

    public function changeStatus($id)
    {
        $this->checkAdminPermission('admin.update');
        $admin = Admin::find($id);
        abort_if($admin->id == 1, 403);
        $status = $admin->status == 'active' ? 'inactive' : 'active';
        $admin->status = $status;
        $admin->save();
        $notification = __('Updated Successfully');

        return response()->json([
            'success' => true,
            'message' => $notification,
        ]);
    }
}
