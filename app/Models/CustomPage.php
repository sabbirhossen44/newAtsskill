<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class CustomPage extends Model
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
        return $this->hasOne(CustomPageTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CustomPageTranslation::class, 'custom_page_id');
    }

    public function getTranslation(string $code): ?CustomPageTranslation
    {
        return $this->hasOne(CustomPageTranslation::class)->where('lang_code', $code)->first();
    }

    public function getNameAttribute(): ?string
    {
        return $this->translation?->name ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->name ?: $this->translations()->first()?->name;
    }

    public function getContentAttribute(): ?string
    {
        return $this->translation?->content ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->content ?: $this->translations()->first()?->content;
    }

    public static function generateUniqueSlug(string $title, ?int $acceptId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while ((static::query())->where('slug', $slug)->where('id', '!=', $acceptId)->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

}
