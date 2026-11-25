<?php

namespace App\Repositories;

use App\Enums\InstructorRequestStatusEnum;
use App\Models\InstructorRequest;
use App\Models\InstructorRequestSetting;
use App\Models\User;
use App\Models\WithdrawMethod;
use App\Services\FileUploadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class InstructorRequestRepository
{
    public static function model()
    {
        return InstructorRequest::class;
    }

    public static function firstForUser(int $userId): ?InstructorRequest
    {
        return static::model()::query()->where('user_id', $userId)->first();
    }

    public static function updatePayout(int $userId, array $data): InstructorRequest
    {
        return static::model()::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'payout_account' => $data['payout_account'],
                'payout_information' => $data['payout_information'],
            ]
        );
    }

    public static function firstSetting(): ?InstructorRequestSetting
    {
        return InstructorRequestSetting::first();
    }

    public static function activeWithdrawMethods(): Collection
    {
        return WithdrawMethod::where('status', 'active')->get();
    }

    public static function submitByRequest(array $data, int $userId, ?UploadedFile $certificate, ?UploadedFile $identityScan): InstructorRequest
    {
        $instructorRequest = static::model()::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'status' => InstructorRequestStatusEnum::PENDING->value,
                'payout_account' => $data['payout_account'],
                'payout_information' => $data['payout_information'],
                'extra_information' => $data['extra_information'] ?? null,
            ]
        );

        if ($certificate) {
            $instructorRequest->certificate = FileUploadService::upload($certificate);
            $instructorRequest->save();
        }

        if ($identityScan) {
            $instructorRequest->identity_scan = FileUploadService::upload($identityScan);
            $instructorRequest->save();
        }

        return $instructorRequest;
    }

    /**
     * Moved from BecomeInstructorController::checkIfApproveInstructor().
     * Note: the old code compared `$user->role !== 'instructor'`, but
     * User casts `role` to UserRoleEnum, so that comparison was always
     * true and the method always returned false. This uses the enum
     * (via isInstructor()), restoring the old site's intended redirect
     * for approved instructors.
     */
    public static function isApprovedInstructor(?User $user): bool
    {
        if (! $user || ! $user->isInstructor()) {
            return false;
        }

        return static::model()::query()->where('user_id', $user->id)
                ->where('status', InstructorRequestStatusEnum::APPROVED->value)->exists()
            || (bool) $user->instructorInfo?->status;
    }
}
