<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Database\Eloquent\Model;

class TranslationService
{
    public static function supportedCodes(): array
    {
        $codes = Language::query()->where('status', '1')->pluck('code')->all();

        return $codes !== [] ? $codes : ['en', 'bn'];
    }

    /**
     * Sync translation rows for a parent model.
     *
     * $translations shape:
     *   ['en' => ['title' => '...', ...], 'bn' => ['title' => '...', ...]]
     * $relation is the HasMany translations relation name.
     */
    public static function sync(Model $model, string $relation, array $translations): void
    {
        $relationQuery = $model->{$relation}();

        foreach ($translations as $langCode => $fields) {
            if (! is_array($fields) || $fields === []) {
                continue;
            }

            $foreignKey = $relationQuery->getForeignKeyName();

            $relationQuery->updateOrCreate(
                ['lang_code' => $langCode],
                array_merge($fields, [$foreignKey => $model->getKey()])
            );
        }
    }

    public static function get(Model $model, string $relation, string $langCode): ?Model
    {
        return $model->{$relation}()->where('lang_code', $langCode)->first();
    }
}
