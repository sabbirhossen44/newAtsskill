<?php

namespace App\Repositories;

use App\Enums\NewsLetterStatusEnum;
use App\Models\NewsLetter;
use Illuminate\Support\Str;

class NewsLetterRepository
{
    public static function model()
    {
        return NewsLetter::class;
    }

    public static function findByVerifyToken(string $token): ?NewsLetter
    {
        return static::model()::query()->where('verify_token', $token)->first();
    }

    public static function storeByRequest(array $data): NewsLetter
    {
        return static::model()::query()->create([
            'email' => $data['email'],
            'verify_token' => Str::random(100),
        ]);
    }

    public static function verify(NewsLetter $newsletter): bool
    {
        return $newsletter->update([
            'verify_token' => Str::random(100),
            'status' => NewsLetterStatusEnum::VERIFIED->value,
        ]);
    }
}
