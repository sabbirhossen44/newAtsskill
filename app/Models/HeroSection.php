<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HeroSection extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function translation(): HasOne
    {
        return $this->hasOne(HeroSectionTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(HeroSectionTranslation::class, 'hero_section_id');
    }

    public function getTranslation(string $code): ?HeroSectionTranslation
    {
        return $this->hasOne(HeroSectionTranslation::class)->where('lang_code', $code)->first();
    }

    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->title ?: $this->translations()->first()?->title;
    }

    public function getSubTitleAttribute(): ?string
    {
        return $this->translation?->sub_title ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->sub_title ?: $this->translations()->first()?->sub_title;
    }

    public function getActionButtonTextAttribute(): ?string
    {
        return $this->translation?->action_button_text ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->action_button_text ?: $this->translations()->first()?->action_button_text;
    }

    public function getVideoButtonTextAttribute(): ?string
    {
        return $this->translation?->video_button_text ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->video_button_text ?: $this->translations()->first()?->video_button_text;
    }

    public function getTotalStudentAttribute(): ?string
    {
        return $this->translation?->total_student ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->total_student ?: $this->translations()->first()?->total_student;
    }

    public function getTotalInstructorAttribute(): ?string
    {
        return $this->translation?->total_instructor ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->total_instructor ?: $this->translations()->first()?->total_instructor;
    }

}
