<?php

namespace App\Repositories;

use App\Models\Setting;
use App\Services\SettingService;

class SettingRepository
{
    public static function model()
    {
        return Setting::class;
    }

    public static function findById(int $id): ?Setting
    {
        return static::model()::query()->find($id);
    }

    public static function findByKey(string $key): ?Setting
    {
        return static::model()::query()->where('key', $key)->first();
    }

    public static function storeByRequest(array $data): Setting
    {
        $setting = static::model()::query()->updateOrCreate(
            ['key' => $data['key']],
            ['value' => $data['value'] ?? '']
        );

        SettingService::forgetCache();

        return $setting;
    }

    public static function updateByRequest(Setting $setting, array $data): bool
    {
        $updated = $setting->update($data);

        SettingService::forgetCache();

        return $updated;
    }

    public static function updateMany(array $data): void
    {
        foreach ($data as $key => $value) {
            SettingService::set($key, $value);
        }
    }
}
