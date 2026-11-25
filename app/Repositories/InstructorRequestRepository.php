<?php

namespace App\Repositories;

use Arafat\LaravelRepository\Repository;

use App\Services\TranslationService;

use Illuminate\Support\Facades\Schema;

use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\InstructorRequest;
use App\Models\InstructorRequestSetting;
use App\Models\User;
use App\Models\WithdrawMethod;
use App\Services\FileUploadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class InstructorRequestRepository extends Repository
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
                'status' => 'pending',
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
                ->where('status', 'approved')->exists()
            || (bool) $user->instructorInfo?->status;
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
        return self::findOrFail($id);
    }

    public static function delete(mixed $record): bool
    {
        if ($record instanceof \Illuminate\Database\Eloquent\Model) {
            return (bool) $record->delete();
        }

        return (bool) static::query()->destroy($record);
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

        $record = self::create($direct);
        static::syncAdminTranslations($record, $translations);

        return $record->fresh();
    }

    public static function updateByRequest(mixed $record, array $data): bool
    {
        [$direct, $translations] = static::splitAdminData($data);

        $updated = self::update($record, $direct);
        static::syncAdminTranslations($record, $translations);

        return (bool) $updated;
    }

}
