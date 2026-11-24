<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public static function resolveUrl(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $disks = array_unique(array_filter(['public', config('filesystems.default')]));

        foreach ($disks as $disk) {
            try {
                if (Storage::disk($disk)->exists($value)) {
                    return Storage::disk($disk)->url($value);
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    public function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => static::resolveUrl($this->src) ?? asset('images/default.webp'),
        );
    }

}
