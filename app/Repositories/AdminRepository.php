<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Services\UploadService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminRepository
{
    public static function model()
    {
        return Admin::class;
    }

    public static function findById(int $id): ?Admin
    {
        return static::model()::query()->find($id);
    }

    public static function findByIdOrFail(int $id): Admin
    {
        return static::model()::query()->findOrFail($id);
    }

    public static function findByEmail(string $email): ?Admin
    {
        return static::model()::query()->where('email', $email)->first();
    }

    public static function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return static::model()::query()->paginate($perPage);
    }

    public static function storeByRequest(array $data): Admin
    {
        // The admin form sends roles under the 'role' key.
        $roles = $data['role'] ?? $data['roles'] ?? [];
        unset($data['role'], $data['roles']);

        $admin = static::model()::query()->create($data);

        if (! empty($roles)) {
            $admin->assignRole($roles);
        }

        return $admin->fresh();
    }

    public static function updateByRequest(Admin $admin, array $data): bool
    {
        $roles = $data['role'] ?? $data['roles'] ?? null;
        unset($data['role'], $data['roles']);

        // An empty password on update means "keep the current password".
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $updated = $admin->update($data);

        if (is_array($roles)) {
            $admin->syncRoles($roles);
        }

        return $updated;
    }

    public static function updateStatus(Admin $admin, string $status): bool
    {
        // Callers must refuse when $admin->id === auth('admin')->id().
        return $admin->update(['status' => $status]);
    }

    /**
     * Toggle active/inactive, as the old AdminController::changeStatus
     * did. Callers must refuse for the super admin (id 1) first.
     */
    public static function toggleStatus(Admin $admin): string
    {
        $status = $admin->status === \App\Enums\UserStatusEnum::ACTIVE ? 'inactive' : 'active';

        $admin->update(['status' => $status]);

        return $status;
    }

    public static function delete(Admin $admin): bool
    {
        return (bool) $admin->delete();
    }

    public static function findByForgetPasswordToken(string $token, ?string $email = null): ?Admin
    {
        $query = static::model()::query()
            ->select('id', 'name', 'email', 'forget_password_token')
            ->where('forget_password_token', $token);

        if ($email !== null) {
            $query->where('email', $email);
        }

        return $query->first();
    }

    public static function issueForgetPasswordToken(Admin $admin): string
    {
        $token = Str::random(100);

        $admin->update(['forget_password_token' => $token]);

        return $token;
    }

    public static function resetPassword(Admin $admin, string $password): bool
    {
        return $admin->update([
            'password' => Hash::make($password),
            'forget_password_token' => null,
        ]);
    }

    /**
     * Own-profile update (name/email/bio + optional avatar image).
     */
    public static function updateProfile(Admin $admin, array $data, ?UploadedFile $image = null): bool
    {
        if ($image) {
            $data['image'] = UploadService::store($image, 'uploads/custom-images/', $admin->image);
        }

        return $admin->update($data);
    }

    /**
     * Change the admin's own password. Returns false when the current
     * password does not match, true when it was changed.
     */
    public static function changePassword(Admin $admin, string $currentPassword, string $newPassword): bool
    {
        if (! Hash::check($currentPassword, $admin->password)) {
            return false;
        }

        return $admin->update(['password' => Hash::make($newPassword)]);
    }
}
