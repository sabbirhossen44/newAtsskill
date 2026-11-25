<?php

namespace App\Models;

use App\Enums\CouponStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'offer_percentage' => 'decimal:2',
            'min_price' => 'decimal:2',
            'expired_date' => 'date',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(CouponHistory::class, 'coupon_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CouponStatusEnum::ACTIVE->value);
    }

    public function isExpired(): bool
    {
        return $this->expired_date !== null && now()->greaterThan($this->expired_date);
    }

}
