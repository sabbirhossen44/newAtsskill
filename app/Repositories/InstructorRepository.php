<?php

namespace App\Repositories;

use App\Models\Instructor;
use App\Services\TranslationService;

class InstructorRepository
{
    public static function model()
    {
        return Instructor::class;
    }

    public static function findById(int $id): ?Instructor
    {
        return static::model()::query()->find($id);
    }

    public static function storeByRequest(array $data): Instructor
    {
        $record = static::model()::query()->create($data);

        return $record->fresh();
    }

    public static function updateByRequest(Instructor $record, array $data): bool
    {
        $updated = $record->update($data);

        return $updated;
    }

    public static function delete(Instructor $record): bool
    {
        return (bool) $record->delete();
    }
}
