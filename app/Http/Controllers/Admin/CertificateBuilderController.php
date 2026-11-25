<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CertificateUpdateRequest;
use App\Repositories\CertificateBuilderRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CertificateBuilderController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('course.certificate.management');
        Paginator::useBootstrap();
        $certificates = CertificateBuilderRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        $certificate = \App\Repositories\CertificateBuilderRepository::findById(1);
                $certificateItems = \App\Models\CertificateBuilderItem::all();
        return view('admin.certificate-builder.index', ['certificates' => $certificates, 'items' => $certificates, 'certificate' => $certificate, 'certificateItems' => $certificateItems]);
    }

    public function create()
    {
        $this->checkAdminPermission('course.certificate.management');
        return view('admin.certificate-builder.create', []);
    }

    public function store(CertificateUpdateRequest $request)
    {
        $this->checkAdminPermission('course.certificate.management');
        $data = $request->validated();
        unset($data['status'], $data['background_image']);
        if ($request->hasFile('background_image')) {
            $data['background'] = UploadService::store($request->file('background_image'));
        }
        if ($request->hasFile('signature')) {
            $data['signature'] = UploadService::store($request->file('signature'));
        }

        CertificateBuilderRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.certificate-builder.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('course.certificate.management');
        $item = CertificateBuilderRepository::findByIdOrFail($id);
        return view('admin.certificate-builder.edit', ['item' => $item, 'certificate' => $item]);
    }

    public function update(CertificateUpdateRequest $request, $id)
    {
        $this->checkAdminPermission('course.certificate.management');
        $item = CertificateBuilderRepository::findByIdOrFail($id);
        $data = $request->validated();
        unset($data['status'], $data['background_image']);
        if ($request->hasFile('background_image')) {
            $data['background'] = UploadService::store($request->file('background_image'), 'uploads/custom-images/', $item->background ?? null);
        }
        if ($request->hasFile('signature')) {
            $data['signature'] = UploadService::store($request->file('signature'), 'uploads/custom-images/', $item->signature ?? null);
        }

        CertificateBuilderRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.certificate-builder.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('course.certificate.management');
        $item = CertificateBuilderRepository::findByIdOrFail($id);

        CertificateBuilderRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.certificate-builder.index');
    }
}
