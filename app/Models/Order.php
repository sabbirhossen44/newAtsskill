<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => App\Enums\OrderStatusEnum::class,
            'has_coupon' => 'boolean',
            'payable_amount' => 'double',
            'gateway_charge' => 'double',
            'payable_with_charge' => 'double',
            'paid_amount' => 'double',
            'conversion_rate' => 'double',
            'coupon_discount_amount' => 'double',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id')->select('id', 'name', 'email', 'image');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id')->select('id', 'name', 'email', 'image');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'order_id', 'id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::PENDING->value);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::COMPLETED->value);
    }

}
