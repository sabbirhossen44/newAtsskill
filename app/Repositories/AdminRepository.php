<?php

namespace App\Repositories;

use App\Models\Admin;
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

    public static function findByEmail(string $email): ?Admin
    {
        return static::model()::query()->where('email', $email)->first();
    }

    public static function storeByRequest(array $data): Admin
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $admin = static::model()::query()->create($data);

        if (! empty($roles)) {
            $admin->assignRole($roles);
        }

        return $admin->fresh();
    }

    public static function updateByRequest(Admin $admin, array $data): bool
    {
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

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
}
