<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CertificateUpdateRequest;
use App\Repositories\CertificateBuilderRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;

class CertificateBuilderController extends Controller
{
    public function index(Request $request)
    {
        $certificates = CertificateBuilderRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        $certificate = \App\Repositories\CertificateBuilderRepository::findById(1);
                $certificateItems = \App\Models\CertificateBuilderItem::all();
        return view('admin.certificate-builder.index', ['certificates' => $certificates, 'items' => $certificates, 'certificate' => $certificate, 'certificateItems' => $certificateItems]);
    }

    public function create()
    {
        return view('admin.certificate-builder.create', []);
    }

    public function store(CertificateUpdateRequest $request)
    {
        $data = $request->validated();
        unset($data['status'], $data['background_image']);
        if ($request->hasFile('background_image')) {
            $data['background'] = UploadService::store($request->file('background_image'));
        }
        if ($request->hasFile('signature')) {
            $data['signature'] = UploadService::store($request->file('signature'));
        }

        CertificateBuilderRepository::storeByRequest($data);

        return redirect()->route('admin.certificate-builder.index')->with('success', 'Certificate created successfully.');
    }

    public function edit($id)
    {
        $item = CertificateBuilderRepository::findByIdOrFail($id);
        return view('admin.certificate-builder.edit', ['item' => $item, 'certificate' => $item]);
    }

    public function update(CertificateUpdateRequest $request, $id)
    {
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

        return redirect()->route('admin.certificate-builder.index')->with('success', 'Certificate updated successfully.');
    }

    public function destroy($id)
    {
        $item = CertificateBuilderRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.certificate-builder.index')->with('success', 'Certificate deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
