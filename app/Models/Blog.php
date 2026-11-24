<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'show_homepage' => 'boolean',
            'is_popular' => 'boolean',
        ];
    }

    public function translation(): HasOne
    {
        return $this->hasOne(BlogTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(BlogTranslation::class, 'blog_id');
    }

    public function getTranslation(string $code): ?BlogTranslation
    {
        return $this->hasOne(BlogTranslation::class)->where('lang_code', $code)->first();
    }

    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->title ?: $this->translations()->first()?->title;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->translation?->description ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->description ?: $this->translations()->first()?->description;
    }

    public function getSeoTitleAttribute(): ?string
    {
        return $this->translation?->seo_title ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->seo_title ?: $this->translations()->first()?->seo_title;
    }

    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->translation?->seo_description ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->seo_description ?: $this->translations()->first()?->seo_description;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class, 'blog_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(BlogMedia::class, 'blog_id');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(BlogMedia::class, 'blog_id')->where('is_primary', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
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
