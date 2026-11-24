<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MenuItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function translation(): HasOne
    {
        return $this->hasOne(MenuItemTranslation::class)->where('lang_code', getSessionLanguage());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MenuItemTranslation::class, 'menu_item_id');
    }

    public function getTranslation(string $code): ?MenuItemTranslation
    {
        return $this->hasOne(MenuItemTranslation::class)->where('lang_code', $code)->first();
    }

    public function getLabelAttribute(): ?string
    {
        return $this->translation?->label ?: $this->translations()->where('lang_code', getDefaultLanguage())->first()?->label ?: $this->translations()->first()?->label;
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function parentMenu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function child(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort', 'ASC');
    }

    public function getSons($id)
    {
        return $this->where('parent_id', $id)->get();
    }

    public function getAll($id)
    {
        return $this->where('menu_id', $id)->orderBy('sort', 'asc')->get();
    }

    public static function getNextSortRoot($menu): int
    {
        return (int) static::where('menu_id', $menu)->max('sort') + 1;
    }

}
