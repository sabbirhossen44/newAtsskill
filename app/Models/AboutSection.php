<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AboutSection extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function translation(): HasOne
    {
        return $this->hasOne(AboutSectionTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AboutSectionTranslation::class, 'about_section_id');
    }

    public function getTranslation(string $code): ?AboutSectionTranslation
    {
        return $this->hasOne(AboutSectionTranslation::class)->where('lang_code', $code)->first();
    }

    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->title ?: $this->translations()->first()?->title;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->translation?->description ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->description ?: $this->translations()->first()?->description;
    }

    public function getButtonTextAttribute(): ?string
    {
        return $this->translation?->button_text ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->button_text ?: $this->translations()->first()?->button_text;
    }

}
