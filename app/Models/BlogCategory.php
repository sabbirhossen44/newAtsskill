<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class BlogCategory extends Model
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
        return $this->hasOne(BlogCategoryTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(BlogCategoryTranslation::class, 'blog_category_id');
    }

    public function getTranslation(string $code): ?BlogCategoryTranslation
    {
        return $this->hasOne(BlogCategoryTranslation::class)->where('lang_code', $code)->first();
    }

    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->title ?: $this->translations()->first()?->title;
    }

    public function getShortDescriptionAttribute(): ?string
    {
        return $this->translation?->short_description ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->short_description ?: $this->translations()->first()?->short_description;
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Blog::class, 'blog_category_id');
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(BlogCategory::class, 'parent_id');
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'parent_id');
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
