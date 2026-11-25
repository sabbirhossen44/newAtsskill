<?php

namespace App\Services;

use App\Http\Controllers\Concerns\FormatsCurrency;
use App\Http\Controllers\Concerns\SessionCart;
use App\Models\Course;
use App\Repositories\CouponRepository;
use App\Repositories\CourseRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Session-cart + coupon logic moved out of CartController / CheckOutController.
 * The cart itself stays session-backed (SessionCart trait) exactly as before:
 * one row per course, keyed by rowId, options carry image/slug/real_price/
 * discount_price. Coupon state lives in the same session keys as the old code
 * (coupon_code / offer_percentage / coupon_discount_amount).
 */
class CartService
{
    use FormatsCurrency;
    use SessionCart;

    public function content(): Collection
    {
        return $this->cartContent();
    }

    public function total(): float
    {
        return $this->cartContent()->sum(fn ($item) => (float) $item->price);
    }

    public function containsCourse(string $id): bool
    {
        return $this->cartContent()->contains(fn ($item) => $item->id == $id);
    }

    public function isOwnCourse(string $id, ?int $userId): bool
    {
        return CourseRepository::isOwnCourse($id, $userId);
    }

    /** Build the cart row for a course (effective price = discount if set). */
    public static function cartDataForCourse(Course $course): array
    {
        return [
            'id' => $course->id,
            'name' => $course->title,
            'qty' => 1,
            'price' => $course->discount > 0 ? $course->discount : $course->price,
            'weight' => 0,
            'options' => [
                'image' => $course->thumbnail,
                'slug' => $course->slug,
                'real_price' => $course->price,
                'discount_price' => $course->discount,
            ],
        ];
    }

    public function addCourse(Course $course): void
    {
        $this->cartAdd(static::cartDataForCourse($course));
        $this->updateCouponDiscountAmount();
    }

    /**
     * Remove a row; returns the removed row as a plain array (for the
     * dataLayer payload) or null when the row did not exist.
     */
    public function remove(string $rowId): ?array
    {
        $cartItem = $this->cartGet($rowId)?->toArray();

        $this->cartRemove($rowId);
        $this->updateCouponDiscountAmount();

        return $cartItem;
    }

    public function destroyCouponSession(): void
    {
        Session::forget('coupon_code');
        Session::forget('offer_percentage');
        Session::forget('coupon_discount_amount');
    }

    public function updateCouponDiscountAmount(): void
    {
        if (! Session::has('coupon_code')) {
            return;
        }

        $coupon = CouponRepository::findActiveByCode(Session::get('coupon_code'));

        if (! $coupon) {
            return;
        }

        Session::put('coupon_discount_amount', ($this->total() * $coupon->offer_percentage) / 100);
    }

    /** Shared cart/coupon totals for the cart and checkout views. */
    public function summary(): array
    {
        if ($this->cartContent()->count() == 0) {
            $this->destroyCouponSession();
        }

        $products = $this->cartContent();
        $cartTotal = $this->total();
        $discountPercent = Session::has('offer_percentage') ? Session::get('offer_percentage') : 0;
        $discountAmount = ($cartTotal * $discountPercent) / 100;

        return [
            'products' => $products,
            'cartTotal' => $cartTotal,
            'discountPercent' => $discountPercent,
            'discountAmount' => $discountAmount,
            'total' => $this->formatCurrency($cartTotal - $discountAmount),
            'coupon' => Session::has('coupon_code') ? Session::get('coupon_code') : '',
        ];
    }

    /**
     * Validate + apply a coupon code. Returns ['ok' => bool, 'message' => string,
     * 'coupon' => ?Coupon] — the controller maps this to the exact JSON
     * responses the old code produced.
     */
    public function applyCoupon(string $code): array
    {
        $coupon = CouponRepository::findActiveByCode($code);

        if (! $coupon) {
            return ['ok' => false, 'message' => __('Invalid coupon'), 'coupon' => null];
        }

        if ($coupon->expired_date < date('Y-m-d')) {
            return ['ok' => false, 'message' => __('Coupon already expired'), 'coupon' => null];
        }

        if ($this->total() < $coupon->min_price) {
            return ['ok' => false, 'message' => __('Minimum order amount should be :amount', ['amount' => $this->formatCurrency($coupon->min_price)]), 'coupon' => null];
        }

        if ($this->total() <= 0) {
            return ['ok' => false, 'message' => __('Cart amount should be greater than 0'), 'coupon' => null];
        }

        /** when coupon will be handle for particular seller or author , above condition will be used  */
        Session::put('coupon_code', $coupon->coupon_code);
        Session::put('offer_percentage', $coupon->offer_percentage);
        Session::put('coupon_discount_amount', ($this->total() * $coupon->offer_percentage) / 100);

        return ['ok' => true, 'message' => __('Coupon applied successful'), 'coupon' => $coupon];
    }

    public function couponResponseData(): array
    {
        $coupon = CouponRepository::findActiveByCode(Session::get('coupon_code', ''));

        return [
            'coupon_code' => Session::get('coupon_code'),
            'offer_percentage' => Session::get('offer_percentage'),
            'discount_amount' => $this->formatCurrency(($this->total() * Session::get('offer_percentage', 0)) / 100),
            'total' => $this->formatCurrency($this->total() - ($this->total() * Session::get('offer_percentage', 0)) / 100),
        ];
    }

    /** Google Tag Manager dataLayer payload for an add-to-cart, or null when GTM is off. */
    public function addToCartDataLayer(Course $course, array $cartData): ?array
    {
        $settings = (object) SettingService::all();
        $marketingSettings = (object) [];

        if ($settings->google_tagmanager_status == 'active' && ($marketingSettings->add_to_cart ?? false)) {
            $cartData['price'] = $this->formatCurrency($course->price);
            $cartData['options']['real_price'] = $this->formatCurrency($course->price);
            $cartData['options']['image'] = asset($course->thumbnail);
            $cartData['options']['slug'] = route('course.show', $course->slug);
            unset($cartData['id']);
            $cartData['user'] = auth('web')->check()
                ? ['name' => auth('web')->user()->name, 'email' => auth('web')->user()->email]
                : 'guest';

            return $cartData;
        }

        return null;
    }

    /** Google Tag Manager dataLayer payload for a remove-from-cart, or null when GTM is off. */
    public function removeFromCartDataLayer(array $cartItem): ?array
    {
        unset($cartItem['rowId'], $cartItem['id']);
        $cartItem['price'] = $this->formatCurrency($cartItem['price']);
        $cartItem['options']['real_price'] = $this->formatCurrency($cartItem['options']['real_price']);
        $cartItem['options']['image'] = asset($cartItem['options']['image']);
        $cartItem['options']['slug'] = route('course.show', $cartItem['options']['slug']);
        $cartItem['user'] = auth('web')->check() ? [
            'name' => auth('web')->user()->name,
            'email' => auth('web')->user()->email,
        ] : 'guest';

        $settings = (object) SettingService::all();
        $marketingSettings = (object) [];

        if ($settings->google_tagmanager_status == 'active' && ($marketingSettings->remove_from_cart ?? false)) {
            return $cartItem;
        }

        return null;
    }
}
