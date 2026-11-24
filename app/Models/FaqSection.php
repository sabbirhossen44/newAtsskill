<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FaqSection extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function translation(): HasOne
    {
        return $this->hasOne(FaqSectionTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FaqSectionTranslation::class, 'faq_section_id');
    }

    public function getTranslation(string $code): ?FaqSectionTranslation
    {
        return $this->hasOne(FaqSectionTranslation::class)->where('lang_code', $code)->first();
    }

    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->title ?: $this->translations()->first()?->title;
    }

    public function getSubTitleAttribute(): ?string
    {
        return $this->translation?->sub_title ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->sub_title ?: $this->translations()->first()?->sub_title;
    }

}
