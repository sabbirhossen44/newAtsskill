<?php

namespace App\Repositories;

use App\Models\EmailTemplate;

class EmailTemplateRepository
{
    public static function model()
    {
        return EmailTemplate::class;
    }

    public static function findByName(string $name): ?EmailTemplate
    {
        return static::model()::query()->where('name', $name)->first();
    }
}
