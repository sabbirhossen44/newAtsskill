<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '1');
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', '1');
    }

    public function isDefault(): bool
    {
        return (string) $this->is_default === '1';
    }

}
