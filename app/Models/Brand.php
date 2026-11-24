<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Brand extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function media(): HasMany
    {
        return $this->hasMany(BrandMedia::class, 'brand_id');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(BrandMedia::class, 'brand_id')->where('is_primary', true);
    }

    public function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->primaryMedia?->media
                ? Media::resolveUrl($this->primaryMedia->media->src)
                : (Media::resolveUrl($this->image) ?? asset('images/default.webp')),
        );
    }

}
