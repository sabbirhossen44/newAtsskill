<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WithdrawStatusEnum;
use App\Http\Controllers\Controller;
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
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $methods = WithdrawMethodRepository::paginateAdmin($request->input('keyword'), $this->perPage($request));

        return view('admin.withdraw-method.index', ['methods' => $methods, 'items' => $methods]);
    }

    public function create()
    {
        return view('admin.withdraw-method.create');
    }

    public function store(WithdrawMethodRequest $request)
    {
        $data = $request->validated();
        $data['min_amount'] = $data['minimum_amount'] ?? 0;
        $data['max_amount'] = $data['maximum_amount'] ?? $data['min_amount'];
        unset($data['minimum_amount'], $data['maximum_amount']);
        WithdrawMethodRepository::storeByRequest($data);

        return redirect()->route('admin.withdraw-method.index')->with('success', 'Withdraw method created successfully.');
    }

    public function edit($id)
    {
        $item = WithdrawMethodRepository::findByIdOrFail((int) $id);

        return view('admin.withdraw-method.edit', ['item' => $item, 'method' => $item]);
    }

    public function update(WithdrawMethodRequest $request, $id)
    {
        $item = WithdrawMethodRepository::findByIdOrFail((int) $id);
        $data = $request->validated();
        $data['min_amount'] = $data['minimum_amount'] ?? 0;
        $data['max_amount'] = $data['maximum_amount'] ?? $data['min_amount'];
        unset($data['minimum_amount'], $data['maximum_amount']);
        WithdrawMethodRepository::updateByRequest($item, $data);

        return redirect()->route('admin.withdraw-method.index')->with('success', 'Withdraw method updated successfully.');
    }

    public function destroy($id)
    {
        $item = WithdrawMethodRepository::findByIdOrFail((int) $id);
        $item->delete();

        return redirect()->route('admin.withdraw-method.index')->with('success', 'Withdraw method deleted successfully.');
    }

    public function withdraw_list(Request $request)
    {
        Paginator::useBootstrap();
        $withdraws = WithdrawRequest::with(['user'])->latest()->paginate($this->perPage($request));

        return view('admin.withdraw.index', ['withdraws' => $withdraws, 'items' => $withdraws]);
    }

    public function pending_withdraw_list(Request $request)
    {
        Paginator::useBootstrap();
        $withdraws = WithdrawRequest::with(['user'])->where('status', WithdrawStatusEnum::PENDING->value)->latest()->paginate($this->perPage($request));

        return view('admin.withdraw.pending', ['withdraws' => $withdraws, 'items' => $withdraws]);
    }

    public function show_withdraw($id)
    {
        $withdraw = WithdrawRequest::with(['user'])->findOrFail($id);

        return view('admin.withdraw.show', compact('withdraw'));
    }

    public function update_withdraw(Request $request, $id)
    {
        $withdraw = WithdrawRequest::findOrFail($id);
        $status = WithdrawStatusEnum::tryFrom((string) $request->input('status')) ?? $withdraw->status;
        $withdraw->update(['status' => $status]);

        return redirect()->back()->with('success', __('Updated Successfully'));
    }

    public function destroy_withdraw($id)
    {
        WithdrawRequest::findOrFail($id)->delete();

        return redirect()->route('admin.withdraw-list')->with('success', __('Deleted Successfully'));
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
