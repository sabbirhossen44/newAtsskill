<?php

namespace App\Repositories;

use App\Enums\UserBannedEnum;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepository
{
    public static function model()
    {
        return User::class;
    }

    public static function findById(int $id): ?User
    {
        return static::model()::query()->find($id);
    }

    public static function findOrFail(int $id): User
    {
        return static::model()::query()->findOrFail($id);
    }

    public static function findByEmail(string $email): ?User
    {
        return static::model()::query()->where('email', $email)->first();
    }

    public static function storeByRequest(array $data): User
    {
        return static::model()::query()->create($data);
    }

    public static function updateByRequest(User $user, array $data): bool
    {
        return $user->update($data);
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
        return $user->update(['role' => UserRoleEnum::INSTRUCTOR->value]);
    }

    public static function registerByRequest(array $data): User
    {
        return static::model()::query()->create([
            'role' => UserRoleEnum::STUDENT->value,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'status' => UserStatusEnum::ACTIVE->value,
            'is_banned' => UserBannedEnum::NO->value,
            'password' => Hash::make($data['password']),
            'verification_token' => Str::random(100),
        ]);
    }

    public static function createSocialUser(array $data): User
    {
        return static::model()::query()->create($data);
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

        return $user->update($data);
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
}
