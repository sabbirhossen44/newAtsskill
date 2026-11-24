<?php

namespace App\Models;

use App\Enums\NewsLetterStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsLetter extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => App\Enums\NewsLetterStatusEnum::class,
        ];
    }

}
