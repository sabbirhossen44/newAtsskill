<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WithdrawStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Enums\RedirectTypeEnum;
use App\Http\Requests\Admin\WithdrawMethodRequest;
use App\Models\WithdrawRequest;
use App\Repositories\WithdrawMethodRepository;
use App\Repositories\WithdrawRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

/**
 * Withdraw methods + withdraw requests. Approving a request credits
 * nothing here (the old controller mailed the instructor and updated
 * the request status); rejection/approval only change status, as the
 * rebuilt show view posts `status` to update-withdraw-status.
 */
class WithdrawController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('withdraw.management');
        Paginator::useBootstrap();
        $methods = WithdrawMethodRepository::paginateAdmin($request->input('keyword'), $this->perPage($request));

        return view('admin.withdraw-method.index', ['methods' => $methods, 'items' => $methods]);
    }

    public function create()
    {
        $this->checkAdminPermission('withdraw.management');

        return view('admin.withdraw-method.create');
    }

    public function store(WithdrawMethodRequest $request)
    {
        $this->checkAdminPermission('withdraw.management');
        $data = $request->validated();
        $data['min_amount'] = $data['minimum_amount'] ?? 0;
        $data['max_amount'] = $data['maximum_amount'] ?? $data['min_amount'];
        unset($data['minimum_amount'], $data['maximum_amount']);
        WithdrawMethodRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.withdraw-method.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('withdraw.management');
        $item = WithdrawMethodRepository::findByIdOrFail((int) $id);

        return view('admin.withdraw-method.edit', ['item' => $item, 'method' => $item]);
    }

    public function update(WithdrawMethodRequest $request, $id)
    {
        $this->checkAdminPermission('withdraw.management');
        $item = WithdrawMethodRepository::findByIdOrFail((int) $id);
        $data = $request->validated();
        $data['min_amount'] = $data['minimum_amount'] ?? 0;
        $data['max_amount'] = $data['maximum_amount'] ?? $data['min_amount'];
        unset($data['minimum_amount'], $data['maximum_amount']);
        WithdrawMethodRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.withdraw-method.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('withdraw.management');
        $item = WithdrawMethodRepository::findByIdOrFail((int) $id);
        WithdrawMethodRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.withdraw-method.index');
    }

    public function withdraw_list(Request $request)
    {
        $this->checkAdminPermission('withdraw.management');
        Paginator::useBootstrap();
        $withdraws = WithdrawRequest::with(['user'])->latest()->paginate($this->perPage($request));

        return view('admin.withdraw.index', ['withdraws' => $withdraws, 'items' => $withdraws]);
    }

    public function pending_withdraw_list(Request $request)
    {
        $this->checkAdminPermission('withdraw.management');
        Paginator::useBootstrap();
        $withdraws = WithdrawRequest::with(['user'])->where('status', WithdrawStatusEnum::PENDING->value)->latest()->paginate($this->perPage($request));

        return view('admin.withdraw.pending', ['withdraws' => $withdraws, 'items' => $withdraws]);
    }

    public function show_withdraw($id)
    {
        $this->checkAdminPermission('withdraw.management');
        $withdraw = WithdrawRequest::with(['user'])->findOrFail($id);

        return view('admin.withdraw.show', compact('withdraw'));
    }

    public function update_withdraw(Request $request, $id)
    {
        $this->checkAdminPermission('withdraw.management');
        $withdraw = WithdrawRequest::findOrFail($id);
        $status = WithdrawStatusEnum::tryFrom((string) $request->input('status')) ?? $withdraw->status;
        $withdraw->update(['status' => $status]);

        return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
    }

    public function destroy_withdraw($id)
    {
        $this->checkAdminPermission('withdraw.management');
        WithdrawRequest::findOrFail($id)->delete();

        return redirect()->route('admin.withdraw-list')->with(['messege' => __('Deleted Successfully'), 'alert-type' => 'success']);
    }
}
