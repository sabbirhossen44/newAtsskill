<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Repositories\CouponRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CouponController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('coupon.management');
        Paginator::useBootstrap();
        $coupons = CouponRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.coupon.index', ['coupons' => $coupons, 'items' => $coupons]);
    }

    public function create()
    {
        $this->checkAdminPermission('coupon.management');
        return view('admin.coupon.create', []);
    }

    public function store(CouponRequest $request)
    {
        $this->checkAdminPermission('coupon.management');
        $data = $request->validated();
        $data['coupon_code'] = $data['code'] ?? $data['coupon_code'] ?? null;
                $data['offer_percentage'] = $data['value'] ?? $data['discount'] ?? $data['offer_percentage'] ?? 0;
                $data['min_price'] = $data['minimum_order_amount'] ?? $data['min_price'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['code'], $data['value'], $data['discount'], $data['minimum_order_amount'], $data['type'], $data['usage_limit']);

        CouponRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.coupon.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('coupon.management');
        $item = CouponRepository::findByIdOrFail($id);
        return view('admin.coupon.edit', ['item' => $item, 'coupon' => $item]);
    }

    public function update(CouponRequest $request, $id)
    {
        $this->checkAdminPermission('coupon.management');
        $item = CouponRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['coupon_code'] = $data['code'] ?? $data['coupon_code'] ?? null;
                $data['offer_percentage'] = $data['value'] ?? $data['discount'] ?? $data['offer_percentage'] ?? 0;
                $data['min_price'] = $data['minimum_order_amount'] ?? $data['min_price'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['code'], $data['value'], $data['discount'], $data['minimum_order_amount'], $data['type'], $data['usage_limit']);

        CouponRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.coupon.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('coupon.management');
        $item = CouponRepository::findByIdOrFail($id);

        CouponRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.coupon.index');
    }
}
