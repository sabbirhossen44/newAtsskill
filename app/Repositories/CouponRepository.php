<?php

namespace App\Repositories;

use App\Models\Coupon;
class CouponRepository
{
    public static function model()
    {
        return Coupon::class;
    }

    public static function findById(int $id): ?Coupon
    {
        return static::model()::query()->find($id);
    }

    public static function findByCode(string $code): ?Coupon
    {
        return static::model()::query()->where('coupon_code', $code)->first();
    }

    public static function findActiveByCode(string $code): ?Coupon
    {
        return static::model()::query()->where(['coupon_code' => $code, 'status' => 'active'])->first();
    }

    public static function storeByRequest(array $data): Coupon
    {
        return static::model()::query()->create($data);
    }

    public static function updateByRequest(Coupon $coupon, array $data): bool
    {
        return $coupon->update($data);
    }
}
