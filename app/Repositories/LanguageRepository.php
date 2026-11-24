<?php

namespace App\Repositories;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;

class LanguageRepository
{
    public static function model()
    {
        return Language::class;
    }

    public static function findById(int $id): ?Language
    {
        return static::model()::query()->find($id);
    }

    public static function findByCode(string $code): ?Language
    {
        return static::model()::query()->where('code', $code)->first();
    }

    public static function storeByRequest(array $data): Language
    {
        return static::model()::query()->create($data);
    }

    public static function updateByRequest(Language $language, array $data): bool
    {
        return $language->update($data);
    }

    public static function makeDefault(Language $language): bool
    {
        static::model()::query()->update(['is_default' => '0']);
        Cache::forget('defaultLanguage');

        return $language->update(['is_default' => '1']);
    }
}
