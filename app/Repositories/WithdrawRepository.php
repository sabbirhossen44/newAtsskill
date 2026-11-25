<?php

namespace App\Repositories;

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
}
