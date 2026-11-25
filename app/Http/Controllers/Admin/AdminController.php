<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use App\Models\Admin;
use App\Repositories\AdminRepository;
use App\Repositories\RoleRepository;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search'));
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return view('admin.admin-list.index', [
            'admins' => $query->latest()->paginate($this->perPage($request))->withQueryString(),
            'filters' => $request->only(['search', 'status', 'per_page']),
        ]);
    }

    public function create()
    {
        $roles = RoleRepository::allRoles();

        return view('admin.admin-list.create', compact('roles'));
    }

    public function store(AdminRequest $request)
    {
        AdminRepository::storeByRequest($request->validated());

        return redirect()->route('admin.admin.index')
            ->with('success', 'Admin created successfully.');
    }

    public function edit($id)
    {
        $admin = AdminRepository::findByIdOrFail($id);
        $roles = RoleRepository::allRoles();

        return view('admin.admin-list.edit', compact('roles', 'admin') + ['item' => $admin]);
    }

    public function update(AdminRequest $request, $id)
    {
        $admin = AdminRepository::findByIdOrFail($id);

        AdminRepository::updateByRequest($admin, $request->validated());

        return redirect()->route('admin.admin.index')
            ->with('success', 'Admin updated successfully.');
    }

    public function destroy($id)
    {
        $admin = AdminRepository::findByIdOrFail($id);

        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();

        return redirect()->route('admin.admin.index')
            ->with('success', 'Admin deleted successfully.');
    }

    public function changeStatus(Request $request, $id)
    {
        $admin = AdminRepository::findByIdOrFail($id);

        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        AdminRepository::toggleStatus($admin);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin status updated successfully.',
            ]);
        }

        return back()->with('success', 'Admin status updated successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
