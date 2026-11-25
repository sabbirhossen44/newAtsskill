<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserBannedEnum;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Models\City;
use App\Models\State;
use App\Repositories\UserEducationRepository;
use App\Repositories\UserExperienceRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

/**
 * Admin customer (student) management. Only the routes the rebuilt
 * views reference are built: lists (all / active / non-verified /
 * banned), show and delete. The old experience/education/ban/mail
 * modals have no rebuilt views, so those actions are not built.
 */
class CustomerController extends Controller
{
    use ChecksAdminPermissions;

    protected function paginateCustomers(Request $request, $query)
    {
        Paginator::useBootstrap();

        return $query->where('role', UserRoleEnum::STUDENT->value)
            ->latest()->paginate($this->perPage($request));
    }

    public function index(Request $request)
    {
        $this->checkAdminPermission('customer.view');
        $customers = $this->paginateCustomers($request, \App\Models\User::query());

        return view('admin.customer.index', compact('customers'));
    }

    public function active_customer(Request $request)
    {
        $this->checkAdminPermission('customer.view');
        $customers = $this->paginateCustomers($request, \App\Models\User::query()->where('status', UserStatusEnum::ACTIVE->value));

        return view('admin.customer.index', compact('customers'));
    }

    public function non_verified_customers(Request $request)
    {
        $this->checkAdminPermission('customer.view');
        $customers = $this->paginateCustomers($request, \App\Models\User::query()->whereNull('email_verified_at'));

        return view('admin.customer.index', compact('customers'));
    }

    public function banned_customers(Request $request)
    {
        $this->checkAdminPermission('customer.view');
        $customers = $this->paginateCustomers($request, \App\Models\User::query()->where('is_banned', UserBannedEnum::YES->value));

        return view('admin.customer.index', compact('customers'));
    }

    public function show($id)
    {
        $this->checkAdminPermission('customer.view');
        $user = UserRepository::findOrFail($id);
        $item = $user;
        $experiences = UserExperienceRepository::allForUser($user->id);
        $educations = UserEducationRepository::allForUser($user->id);
        $banned_histories = \App\Models\BannedHistory::where('user_id', $id)->orderBy('id', 'desc')->get();
        $states = State::where(['country_id' => $user->country_id, 'status' => 1])->get();
        $cities = City::where(['state_id' => $user->state_id, 'status' => 1])->get();

        return view('admin.customer.show', compact('user', 'item', 'experiences', 'educations', 'banned_histories', 'states', 'cities'));
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('customer.delete');
        $user = UserRepository::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.all-customers')->with(['messege' => __('Deleted Successfully'), 'alert-type' => 'success']);
    }
}
