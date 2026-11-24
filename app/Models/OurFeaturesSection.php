<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OurFeaturesSection extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function translation(): HasOne
    {
        return $this->hasOne(OurFeaturesSectionTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(OurFeaturesSectionTranslation::class, 'our_features_section_id');
    }

    public function getTranslation(string $code): ?OurFeaturesSectionTranslation
    {
        return $this->hasOne(OurFeaturesSectionTranslation::class)->where('lang_code', $code)->first();
    }

    public function getTitleOneAttribute(): ?string
    {
        return $this->translation?->title_one ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->title_one ?: $this->translations()->first()?->title_one;
    }

    public function getSubTitleOneAttribute(): ?string
    {
        return $this->translation?->sub_title_one ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->sub_title_one ?: $this->translations()->first()?->sub_title_one;
    }

    public function getTitleTwoAttribute(): ?string
    {
        return $this->translation?->title_two ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->title_two ?: $this->translations()->first()?->title_two;
    }

    public function getSubTitleTwoAttribute(): ?string
    {
        return $this->translation?->sub_title_two ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->sub_title_two ?: $this->translations()->first()?->sub_title_two;
    }

    public function getTitleThreeAttribute(): ?string
    {
        return $this->translation?->title_three ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->title_three ?: $this->translations()->first()?->title_three;
    }

    public function getSubTitleThreeAttribute(): ?string
    {
        return $this->translation?->sub_title_three ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->sub_title_three ?: $this->translations()->first()?->sub_title_three;
    }

    public function getTitleFourAttribute(): ?string
    {
        return $this->translation?->title_four ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->title_four ?: $this->translations()->first()?->title_four;
    }

    public function getSubTitleFourAttribute(): ?string
    {
        return $this->translation?->sub_title_four ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->sub_title_four ?: $this->translations()->first()?->sub_title_four;
    }

}
