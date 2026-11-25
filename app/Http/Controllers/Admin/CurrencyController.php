<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CurrencyRequest;
use App\Repositories\CurrencyRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CurrencyController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('currency.view');
        Paginator::useBootstrap();
        $currencies = CurrencyRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.currency.index', ['currencies' => $currencies, 'items' => $currencies]);
    }

    public function create()
    {
        $this->checkAdminPermission('currency.view');
        return view('admin.currency.create', []);
    }

    public function store(CurrencyRequest $request)
    {
        $this->checkAdminPermission('currency.view');
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

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.currency.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('currency.view');
        $item = CurrencyRepository::findByIdOrFail($id);
        return view('admin.currency.edit', ['item' => $item, 'currency' => $item]);
    }

    public function update(CurrencyRequest $request, $id)
    {
        $this->checkAdminPermission('currency.view');
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

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.currency.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('currency.view');
        $item = CurrencyRepository::findByIdOrFail($id);

        CurrencyRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.currency.index');
    }
}
