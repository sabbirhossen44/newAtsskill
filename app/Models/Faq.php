<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Faq extends Model
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
        return $this->hasOne(FaqTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FaqTranslation::class, 'faq_id');
    }

    public function getTranslation(string $code): ?FaqTranslation
    {
        return $this->hasOne(FaqTranslation::class)->where('lang_code', $code)->first();
    }

    public function getQuestionAttribute(): ?string
    {
        return $this->translation?->question ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->question ?: $this->translations()->first()?->question;
    }

    public function getAnswerAttribute(): ?string
    {
        return $this->translation?->answer ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->answer ?: $this->translations()->first()?->answer;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 0);
    }

}
