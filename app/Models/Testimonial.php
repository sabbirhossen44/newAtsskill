<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Testimonial extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function translation(): HasOne
    {
        return $this->hasOne(TestimonialTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TestimonialTranslation::class, 'testimonial_id');
    }

    public function getTranslation(string $code): ?TestimonialTranslation
    {
        return $this->hasOne(TestimonialTranslation::class)->where('lang_code', $code)->first();
    }

    public function getNameAttribute(): ?string
    {
        return $this->translation?->name ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->name ?: $this->translations()->first()?->name;
    }

    public function getDesignationAttribute(): ?string
    {
        return $this->translation?->designation ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->designation ?: $this->translations()->first()?->designation;
    }

    public function getCommentAttribute(): ?string
    {
        return $this->translation?->comment ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->comment ?: $this->translations()->first()?->comment;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 0);
    }

    public function media(): HasMany
    {
        return $this->hasMany(TestimonialMedia::class, 'testimonial_id');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(TestimonialMedia::class, 'testimonial_id')->where('is_primary', true);
    }

}
