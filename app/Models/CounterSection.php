<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CounterSection extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function translation(): HasOne
    {
        return $this->hasOne(CounterSectionTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CounterSectionTranslation::class, 'counter_section_id');
    }

    public function getTranslation(string $code): ?CounterSectionTranslation
    {
        return $this->hasOne(CounterSectionTranslation::class)->where('lang_code', $code)->first();
    }

    public function getSectionTitleAttribute(): ?string
    {
        return $this->translation?->section_title ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->section_title ?: $this->translations()->first()?->section_title;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->translation?->description ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->description ?: $this->translations()->first()?->description;
    }

}
