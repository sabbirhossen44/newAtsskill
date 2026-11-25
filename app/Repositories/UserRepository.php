<?php

namespace App\Repositories;

use Arafat\LaravelRepository\Repository;

use App\Services\TranslationService;

use Illuminate\Support\Facades\Schema;

use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepository extends Repository
{
    public static function model()
    {
        return User::class;
    }

    public static function findById(int $id): ?User
    {
        return self::find($id);
    }

    public static function findOrFail($primaryKey): User
    {
        return static::model()::query()->findOrFail($primaryKey);
    }

    public static function findByEmail(string $email): ?User
    {
        return static::model()::query()->where('email', $email)->first();
    }

    public static function storeByRequest(array $data): User
    {
        return self::create($data);
    }

    public static function updateByRequest(User $user, array $data): bool
    {
        return self::update($user, $data);
    }

    public static function updateStatus(User $user, string $status): bool
    {
        return $user->update(['status' => $status]);
    }

    public static function ban(User $user): bool
    {
        return $user->update(['is_banned' => 'yes']);
    }

    public static function unban(User $user): bool
    {
        return $user->update(['is_banned' => 'no']);
    }

    public static function makeInstructor(User $user): bool
    {
        return $user->update(['role' => 'instructor']);
    }

    public static function registerByRequest(array $data): User
    {
        return self::create([
            'role' => 'student',
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'status' => 'active',
            'is_banned' => 'no',
            'password' => Hash::make($data['password']),
            'verification_token' => Str::random(100),
        ]);
    }

    public static function createSocialUser(array $data): User
    {
        return self::create($data);
    }

    public static function findByVerificationToken(string $token): ?User
    {
        return static::model()::query()->where('verification_token', $token)->first();
    }

    public static function markEmailVerified(User $user): bool
    {
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->verification_token = null;

        return $user->save();
    }

    public static function findByForgetPasswordToken(string $token): ?User
    {
        return static::model()::query()
            ->select('id', 'name', 'email', 'forget_password_token')
            ->where('forget_password_token', $token)
            ->first();
    }

    public static function findByForgetPasswordTokenAndEmail(string $token, string $email): ?User
    {
        return static::model()::query()
            ->select('id', 'name', 'email', 'forget_password_token')
            ->where('forget_password_token', $token)
            ->where('email', $email)
            ->first();
    }

    public static function setForgetPasswordToken(User $user): User
    {
        $user->forget_password_token = Str::random(100);
        $user->save();

        return $user;
    }

    public static function resetCustomPassword(User $user, string $password): bool
    {
        $user->password = Hash::make($password);
        $user->forget_password_token = null;

        return $user->save();
    }

    public static function updatePassword(User $user, string $password): bool
    {
        return $user->update([
            'password' => Hash::make($password),
        ]);
    }

    public static function updateProfileByRequest(User $user, array $data, ?UploadedFile $image = null, ?UploadedFile $cover = null): bool
    {
        unset($data['image'], $data['cover']);

        if ($image) {
            $data['image'] = FileUploadService::upload($image, 'uploads/custom-images/');
        }

        if ($cover) {
            $data['cover'] = FileUploadService::upload($cover, 'uploads/custom-images/');
        }

        return self::update($user, $data);
    }

    public static function updateBioByRequest(User $user, array $data): bool
    {
        return $user->update([
            'job_title' => $data['designation'],
            'bio' => $data['bio'],
            'short_bio' => $data['short_bio'],
        ]);
    }

    public static function updateAddressByRequest(User $user, array $data): bool
    {
        return $user->update([
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country_id' => $data['country'],
        ]);
    }

    public static function updateSocialsByRequest(User $user, array $data): bool
    {
        return $user->update([
            'facebook' => $data['facebook'] ?? null,
            'twitter' => $data['twitter'] ?? null,
            'website' => $data['website'] ?? null,
            'linkedin' => $data['linkedin'] ?? null,
            'github' => $data['github'] ?? null,
        ]);
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

}
