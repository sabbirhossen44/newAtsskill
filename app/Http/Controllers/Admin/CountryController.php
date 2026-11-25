<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CountryStoreRequest;
use App\Repositories\CountryRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CountryController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('location.view');
        Paginator::useBootstrap();
        $countries = CountryRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.country.index', ['countries' => $countries, 'items' => $countries]);
    }

    public function create()
    {
        $this->checkAdminPermission('location.view');
        return view('admin.country.create', []);
    }

    public function store(CountryStoreRequest $request)
    {
        $this->checkAdminPermission('location.view');
        $data = $request->validated();
        $data['status'] = ($data['status'] ?? null) === 'active' || ($data['status'] ?? null) === 1 || ($data['status'] ?? null) === '1' ? 1 : 0;
                unset($data['code']);

        CountryRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.country.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('location.view');
        $item = CountryRepository::findByIdOrFail($id);
        return view('admin.country.edit', ['item' => $item, 'country' => $item]);
    }

    public function update(CountryStoreRequest $request, $id)
    {
        $this->checkAdminPermission('location.view');
        $item = CountryRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['status'] = ($data['status'] ?? null) === 'active' || ($data['status'] ?? null) === 1 || ($data['status'] ?? null) === '1' ? 1 : 0;
                unset($data['code']);

        CountryRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.country.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('location.view');
        $item = CountryRepository::findByIdOrFail($id);

        CountryRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.country.index');
    }
}
