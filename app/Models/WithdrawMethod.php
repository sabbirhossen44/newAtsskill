<?php

namespace App\Models;

use App\Enums\ChapterStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WithdrawMethod extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => App\Enums\ChapterStatusEnum::class,
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
        ];
    }

    public function requests(): HasMany
    {
        return $this->hasMany(WithdrawRequest::class, 'method', 'name');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ChapterStatusEnum::ACTIVE->value);
    }

}
