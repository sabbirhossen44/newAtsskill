<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Repositories\FaqRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class FaqController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('faq.view');
        Paginator::useBootstrap();
        $faqs = FaqRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.faq.index', ['faqs' => $faqs, 'items' => $faqs]);
    }

    public function create()
    {
        $this->checkAdminPermission('faq.view');
        return view('admin.faq.create', []);
    }

    public function store(FaqRequest $request)
    {
        $this->checkAdminPermission('faq.view');
        $data = $request->validated();

        FaqRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.faq.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('faq.view');
        $item = FaqRepository::findByIdOrFail($id);
        return view('admin.faq.edit', ['item' => $item, 'faq' => $item]);
    }

    public function update(FaqRequest $request, $id)
    {
        $this->checkAdminPermission('faq.view');
        $item = FaqRepository::findByIdOrFail($id);
        $data = $request->validated();

        FaqRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.faq.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('faq.view');
        $item = FaqRepository::findByIdOrFail($id);

        FaqRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.faq.index');
    }

    public function statusUpdate(Request $request, $id)
    {
        $this->checkAdminPermission('faq.view');
        $item = FaqRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            FaqRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
        }

        FaqRepository::toggleStatus($item);

        return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
    }
}
