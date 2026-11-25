<?php

namespace App\Repositories;

use App\Services\TranslationService;

use Illuminate\Support\Facades\Schema;

use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\Course;
use App\Models\InstructorRequest;
use App\Models\OrderItem;
use App\Models\WithdrawMethod;
use App\Models\WithdrawRequest;

class WithdrawRepository
{
    public static function model()
    {
        return WithdrawRequest::class;
    }

    public static function totalApprovedForUser(int $userId): float
    {
        return (float) WithdrawRequest::where(['user_id' => $userId, 'status' => 'approved'])->sum('withdraw_amount');
    }

    public static function paginateForUser(int $userId, int $perPage = 30)
    {
        return WithdrawRequest::where('user_id', $userId)->orderBy('id', 'desc')->paginate($perPage);
    }

    /** Payout index data: sold count, requests, total withdrawn. */
    public static function payoutIndexData(int $userId): array
    {
        $courseIds = Course::where('instructor_id', $userId)->pluck('id')->toArray();
        $totalCourseSold = OrderItem::whereIn('course_id', $courseIds)->count();
        $withdrawRequests = static::paginateForUser($userId);
        $totalWithdraw = static::totalApprovedForUser($userId);

        return compact('totalCourseSold', 'withdrawRequests', 'totalWithdraw');
    }

    /** Payout create data: sold count, gateway request, method, total withdrawn. */
    public static function payoutCreateData(int $userId): array
    {
        $courseIds = Course::where('instructor_id', $userId)->pluck('id')->toArray();
        $totalCourseSold = OrderItem::whereIn('course_id', $courseIds)->count();
        $gateway = InstructorRequest::where('user_id', $userId)->first();
        $withdrawMethod = WithdrawMethod::where('name', $gateway->payout_account)->first();
        $totalWithdraw = static::totalApprovedForUser($userId);

        return compact('totalCourseSold', 'gateway', 'totalWithdraw', 'withdrawMethod');
    }

    /**
     * Validate a payout amount against the wallet balance, the method
     * limits and any pending request. Returns the error message, or null
     * when the request is valid.
     */
    public static function validatePayout(int $userId, float $walletBalance, $amount): ?string
    {
        $gateway = InstructorRequest::where('user_id', $userId)->first();
        $withdrawMethod = WithdrawMethod::where('name', $gateway->payout_account)->first();

        if ($amount < 0 || $amount > $walletBalance) {
            return __('Invalid amount');
        } elseif ($withdrawMethod->min_amount > $amount) {
            return __('Minimum payout amount is :amount', ['amount' => $withdrawMethod->min_amount]);
        } elseif ($withdrawMethod->max_amount < $amount) {
            return __('Maximum payout amount is :amount', ['amount' => $withdrawMethod->max_amount]);
        } elseif (WithdrawRequest::where('user_id', $userId)->where('status', 'pending')->exists()) {
            return __('You already have a pending payout request.');
        }

        return null;
    }

    public static function storePayoutRequest(int $userId, float $walletBalance, $amount): WithdrawRequest
    {
        $gateway = InstructorRequest::where('user_id', $userId)->first();

        return WithdrawRequest::create([
            'user_id' => $userId,
            'withdraw_amount' => $amount,
            'method' => $gateway->payout_account,
            'account_info' => $gateway->payout_information,
            'status' => 'pending',
            'current_amount' => $walletBalance,
        ]);
    }

    /** Delete a pending payout request of the user. True when one was deleted. */
    public static function deletePending(string $id, int $userId): bool
    {
        $request = WithdrawRequest::where('id', $id)->where(['user_id' => $userId, 'status' => 'pending'])->first();

        if ($request) {
            $request->delete();

            return true;
        }

        return false;
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

    public static function updateStatus(mixed $record, mixed $status): bool
    {
        return (bool) $record->update(['status' => static::normalizeStatusValue($record, $status)]);
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

    public static function storeByRequest(array $data): mixed
    {
        [$direct, $translations] = static::splitAdminData($data);

        $record = static::model()::query()->create($direct);
        static::syncAdminTranslations($record, $translations);

        return $record->fresh();
    }

    public static function updateByRequest(mixed $record, array $data): bool
    {
        [$direct, $translations] = static::splitAdminData($data);

        $updated = $record->update($direct);
        static::syncAdminTranslations($record, $translations);

        return (bool) $updated;
    }

}
