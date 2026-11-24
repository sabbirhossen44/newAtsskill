<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public const CACHE_KEY = 'site_settings';

    public static function all(): array
    {
        try {
            return Cache::rememberForever(static::CACHE_KEY, function () {
                return Setting::query()->pluck('value', 'key')->all();
            });
        } catch (\Throwable) {
            // Fresh install / tests before migrate: no settings table yet.
            return [];
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): Setting
    {
        $setting = Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_string($value) ? $value : (string) $value]
        );

        static::forgetCache();

        return $setting;
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }
}
