<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Menu extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function translation(): HasOne
    {
        return $this->hasOne(MenuTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MenuTranslation::class, 'menu_id');
    }

    public function getTranslation(string $code): ?MenuTranslation
    {
        return $this->hasOne(MenuTranslation::class)->where('lang_code', $code)->first();
    }

    public function getNameAttribute(): ?string
    {
        return $this->translation?->name ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->name ?: $this->translations()->first()?->name;
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')->with('child')->where('parent_id', 0)->orderBy('sort', 'ASC');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')->orderBy('sort', 'ASC');
    }

    public static function bySlug(string $slug): ?static
    {
        return static::where('slug', $slug)->first();
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
