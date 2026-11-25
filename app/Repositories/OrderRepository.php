<?php

namespace App\Repositories;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
class OrderRepository
{
    public static function model()
    {
        return Order::class;
    }

    public static function findById(int $id): ?Order
    {
        return static::model()::query()->find($id);
    }

    public static function storeByRequest(array $data): Order
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $order = static::model()::query()->create($data);

        foreach ($items as $item) {
            $order->orderItems()->create($item);
        }

        return $order->fresh();
    }

    public static function updateByRequest(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    public static function updateStatus(Order $order, string $status): bool
    {
        return $order->update(['status' => $status]);
    }

    public static function latestForBuyer(int $buyerId, int $limit = 10)
    {
        return static::model()::query()
            ->where('buyer_id', $buyerId)
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get();
    }

    public static function paginateForBuyer(int $buyerId, int $perPage = 30)
    {
        return static::model()::query()
            ->where('buyer_id', $buyerId)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public static function findForBuyerOrFail(int $id, int $buyerId): Order
    {
        return static::model()::query()
            ->where('id', $id)
            ->where('buyer_id', $buyerId)
            ->firstOrFail();
    }

    /**
     * Create an order from cart items, with its order items and enrolments.
     * $items are ['price' => ..., 'course_id' => ...] arrays. When
     * $creditInstructorWallet is true the instructor keeps the price minus
     * the order commission_rate % (the platform keeps the commission).
     */
    public static function createFromCart(array $attributes, array $items, int $buyerId, bool $creditInstructorWallet = false): Order
    {
        $order = static::model()::query()->create($attributes);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'price' => $item['price'],
                'course_id' => $item['course_id'],
                'commission_rate' => $order->commission_rate,
            ]);
            Enrollment::create([
                'order_id' => $order->id,
                'user_id' => $buyerId,
                'course_id' => $item['course_id'],
                'has_access' => 1,
            ]);

            if ($creditInstructorWallet) {
                $commissionAmount = $item['price'] * ($order->commission_rate / 100);
                $amountAfterCommission = $item['price'] - $commissionAmount;
                $instructor = Course::find($item['course_id'])?->instructor;
                $instructor?->increment('wallet_balance', $amountAfterCommission);
            }
        }

        return $order->fresh();
    }

    public static function countSoldForCourseIds(array $courseIds): int
    {
        return OrderItem::query()->whereIn('course_id', $courseIds)->count();
    }

    public static function countPendingOrdersForCourseIds(array $courseIds): int
    {
        return OrderItem::query()->whereIn('course_id', $courseIds)->whereHas('order', function ($q) {
            $q->where('status', 'pending');
        })->count();
    }

    /** Paid order items for an instructor's courses (my-sells list). */
    public static function mySellsForInstructor(int $instructorId)
    {
        $courseIds = CourseRepository::instructorCourseIds($instructorId);

        return OrderItem::query()->whereIn('course_id', $courseIds)->with(['order', 'course'])
            ->whereHas('order', function ($q) {
                $q->where('payment_status', 'paid');
            })->orderBy('id', 'desc')->paginate(30);
    }
}
