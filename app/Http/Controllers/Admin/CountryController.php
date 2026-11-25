<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CountryStoreRequest;
use App\Repositories\CountryRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $countries = CountryRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.country.index', ['countries' => $countries, 'items' => $countries]);
    }

    public function create()
    {
        return view('admin.country.create', []);
    }

    public function store(CountryStoreRequest $request)
    {
        $data = $request->validated();
        $data['status'] = ($data['status'] ?? null) === 'active' || ($data['status'] ?? null) === 1 || ($data['status'] ?? null) === '1' ? 1 : 0;
                unset($data['code']);

        CountryRepository::storeByRequest($data);

        return redirect()->route('admin.country.index')->with('success', 'Country created successfully.');
    }

    public function edit($id)
    {
        $item = CountryRepository::findByIdOrFail($id);
        return view('admin.country.edit', ['item' => $item, 'country' => $item]);
    }

    public function update(CountryStoreRequest $request, $id)
    {
        $item = CountryRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['status'] = ($data['status'] ?? null) === 'active' || ($data['status'] ?? null) === 1 || ($data['status'] ?? null) === '1' ? 1 : 0;
                unset($data['code']);

        CountryRepository::updateByRequest($item, $data);

        return redirect()->route('admin.country.index')->with('success', 'Country updated successfully.');
    }

    public function destroy($id)
    {
        $item = CountryRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.country.index')->with('success', 'Country deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
