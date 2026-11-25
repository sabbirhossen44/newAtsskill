<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Repositories\CouponRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $coupons = CouponRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.coupon.index', ['coupons' => $coupons, 'items' => $coupons]);
    }

    public function create()
    {
        return view('admin.coupon.create', []);
    }

    public function store(CouponRequest $request)
    {
        $data = $request->validated();
        $data['coupon_code'] = $data['code'] ?? $data['coupon_code'] ?? null;
                $data['offer_percentage'] = $data['value'] ?? $data['discount'] ?? $data['offer_percentage'] ?? 0;
                $data['min_price'] = $data['minimum_order_amount'] ?? $data['min_price'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['code'], $data['value'], $data['discount'], $data['minimum_order_amount'], $data['type'], $data['usage_limit']);

        CouponRepository::storeByRequest($data);

        return redirect()->route('admin.coupon.index')->with('success', 'Coupon created successfully.');
    }

    public function edit($id)
    {
        $item = CouponRepository::findByIdOrFail($id);
        return view('admin.coupon.edit', ['item' => $item, 'coupon' => $item]);
    }

    public function update(CouponRequest $request, $id)
    {
        $item = CouponRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['coupon_code'] = $data['code'] ?? $data['coupon_code'] ?? null;
                $data['offer_percentage'] = $data['value'] ?? $data['discount'] ?? $data['offer_percentage'] ?? 0;
                $data['min_price'] = $data['minimum_order_amount'] ?? $data['min_price'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['code'], $data['value'], $data['discount'], $data['minimum_order_amount'], $data['type'], $data['usage_limit']);

        CouponRepository::updateByRequest($item, $data);

        return redirect()->route('admin.coupon.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy($id)
    {
        $item = CouponRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.coupon.index')->with('success', 'Coupon deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
