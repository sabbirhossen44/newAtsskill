<?php

namespace App\Repositories;

use App\Services\TranslationService;

use Illuminate\Support\Facades\Schema;

use Illuminate\Pagination\LengthAwarePaginator;

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

    public static function paginateAdmin(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = static::model()::query()->latest();

        if ($search !== null && $search !== '') {
            $table = (new (static::model()))->getTable();
            $query->where(function ($q) use ($search, $table) {
                foreach (['name', 'title', 'email', 'code', 'slug', 'coupon_code'] as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $q->orWhere($column, 'like', '%'.$search.'%');
                    }
                }
            });
        }

        return $query->paginate($perPage);
    }

    public static function findByIdOrFail(int $id): mixed
    {
        return static::model()::query()->findOrFail($id);
    }

    public static function delete(mixed $record): bool
    {
        return (bool) $record->delete();
    }

    public static function toggleStatus(mixed $record): bool
    {
        $raw = $record->getAttributes()['status'] ?? null;

        if (is_string($raw) && in_array($raw, ['active', 'inactive'], true)) {
            $new = $raw === 'active' ? 'inactive' : 'active';
        } else {
            $new = $raw ? 0 : 1;
        }

        return (bool) $record->update(['status' => $new]);
    }

    protected static function normalizeStatusValue(mixed $record, mixed $status): mixed
    {
        if (! in_array($status, ['active', 'inactive'], true)) {
            return $status;
        }

        $casts = $record->getCasts();
        $cast = $casts['status'] ?? null;

        if (in_array($cast, ['boolean', 'bool', 'int', 'integer'], true)) {
            return $status === 'active' ? 1 : 0;
        }

        if (is_string($cast) && enum_exists($cast)) {
            return $status;
        }

        try {
            $type = Schema::getColumnType($record->getTable(), 'status');
            if (in_array($type, ['boolean', 'tinyint', 'smallint', 'integer', 'bigint'], true)) {
                return $status === 'active' ? 1 : 0;
            }
        } catch (\Throwable) {
        }

        return $status;
    }

    /**
     * Split validated admin data into direct table columns and translation
     * fields. Form fields that are neither (e.g. a display-only `code`)
     * are dropped unless the model has a `translations` relation, in
     * which case they are synced for the request language (default `en`).
     * Status strings ('active'/'inactive') are normalised to the column
     * type (boolean columns get 1/0; enum-cast columns keep the string).
     */
    protected static function splitAdminData(array $data): array
    {
        $model = new (static::model());
        $columns = Schema::getColumnListing($model->getTable());
        $data = array_diff_key($data, array_flip(['_token', '_method']));
        $translations = $data['translations'] ?? null;
        unset($data['translations']);

        if (array_key_exists('status', $data) && in_array('status', $columns, true)) {
            $data['status'] = static::normalizeStatusValue($model, $data['status']);
        }

        $direct = array_intersect_key($data, array_flip($columns));
        $rest = array_diff_key($data, array_flip($columns));

        if ($translations === null && $rest !== [] && method_exists($model, 'translations')) {
            $lang = 'en';
            if (is_string($data['code'] ?? null) && ! in_array('code', $columns, true)) {
                $lang = $data['code'];
            }
            $translations = [$lang => $rest];
        }

        return [$direct, is_array($translations) ? $translations : []];
    }

    protected static function syncAdminTranslations(mixed $record, array $translations): void
    {
        if ($translations !== [] && method_exists($record, 'translations')) {
            TranslationService::sync($record, 'translations', $translations);
        }
    }


    public static function paginateAdminOrders(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        $query = static::model()::query()->with(['user'])->latest();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_id', 'like', '%'.$search.'%')
                    ->orWhere('order_id', 'like', '%'.$search.'%');
            });
        }

        return $query->paginate($perPage);
    }

    public static function paginatePendingOrders(int $perPage = 15): LengthAwarePaginator
    {
        return static::model()::query()->with(['user'])
            ->where('payment_status', \App\Enums\PaymentStatusEnum::PENDING->value)
            ->latest()->paginate($perPage);
    }

    public static function findAdminOrFail(int $id): Order
    {
        return static::model()::query()->with(['user', 'orderItems'])->findOrFail($id);
    }

    public static function updateAdminOrder(Order $order, array $data): bool
    {
        return $order->update($data);
    }

}
