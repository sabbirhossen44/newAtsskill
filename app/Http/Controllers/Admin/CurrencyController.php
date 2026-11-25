<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CurrencyRequest;
use App\Repositories\CurrencyRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $currencies = CurrencyRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.currency.index', ['currencies' => $currencies, 'items' => $currencies]);
    }

    public function create()
    {
        return view('admin.currency.create', []);
    }

    public function store(CurrencyRequest $request)
    {
        $data = $request->validated();
        $data['currency_name'] = $data['name'] ?? $data['currency_name'] ?? null;
                $data['currency_code'] = $data['code'] ?? $data['currency_code'] ?? null;
                $data['currency_icon'] = $data['symbol'] ?? $data['currency_icon'] ?? null;
                $data['currency_rate'] = $data['exchange_rate'] ?? $data['currency_rate'] ?? 0;
                $data['country_code'] = $data['currency_code'];
                $data['is_default'] = 'no';
                $data['currency_position'] = 'before_price';
                $data['status'] = ($data['status'] ?? null) === 'active' || ($data['status'] ?? null) === 1 || ($data['status'] ?? null) === '1' ? 1 : 0;
                unset($data['name'], $data['code'], $data['symbol'], $data['exchange_rate']);

        CurrencyRepository::storeByRequest($data);

        return redirect()->route('admin.currency.index')->with('success', 'Currency created successfully.');
    }

    public function edit($id)
    {
        $item = CurrencyRepository::findByIdOrFail($id);
        return view('admin.currency.edit', ['item' => $item, 'currency' => $item]);
    }

    public function update(CurrencyRequest $request, $id)
    {
        $item = CurrencyRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['currency_name'] = $data['name'] ?? $data['currency_name'] ?? null;
                $data['currency_code'] = $data['code'] ?? $data['currency_code'] ?? null;
                $data['currency_icon'] = $data['symbol'] ?? $data['currency_icon'] ?? null;
                $data['currency_rate'] = $data['exchange_rate'] ?? $data['currency_rate'] ?? 0;
                $data['country_code'] = $data['currency_code'];
                $data['is_default'] = 'no';
                $data['currency_position'] = 'before_price';
                $data['status'] = ($data['status'] ?? null) === 'active' || ($data['status'] ?? null) === 1 || ($data['status'] ?? null) === '1' ? 1 : 0;
                unset($data['name'], $data['code'], $data['symbol'], $data['exchange_rate']);

        CurrencyRepository::updateByRequest($item, $data);

        return redirect()->route('admin.currency.index')->with('success', 'Currency updated successfully.');
    }

    public function destroy($id)
    {
        $item = CurrencyRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.currency.index')->with('success', 'Currency deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
