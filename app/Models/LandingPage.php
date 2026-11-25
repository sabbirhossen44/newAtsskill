<?php

namespace App\Models;

use App\Enums\LandingPageStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LandingPage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\LandingPageStatusEnum::class,
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug) && ! empty($model->getAttributes()['title'] ?? null)) {
                $model->slug = static::generateUniqueSlug($model->getAttributes()['title']);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && ! empty($model->getAttributes()['title'] ?? null)) {
                $model->slug = static::generateUniqueSlug($model->getAttributes()['title'], $model->id);
            }
        });
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
