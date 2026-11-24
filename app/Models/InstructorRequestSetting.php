<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InstructorRequestSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function translation(): HasOne
    {
        return $this->hasOne(InstructorRequestSettingTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(InstructorRequestSettingTranslation::class, 'instructor_request_setting_id');
    }

    public function getTranslation(string $code): ?InstructorRequestSettingTranslation
    {
        return $this->hasOne(InstructorRequestSettingTranslation::class)->where('lang_code', $code)->first();
    }

    public function getInstructionsAttribute(): ?string
    {
        return $this->translation?->instructions ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->instructions ?: $this->translations()->first()?->instructions;
    }

}
