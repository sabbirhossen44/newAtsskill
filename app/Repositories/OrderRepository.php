<?php

namespace App\Repositories;

use App\Models\Order;
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
}
