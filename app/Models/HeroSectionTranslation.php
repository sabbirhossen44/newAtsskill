<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroSectionTranslation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function heroSection(): BelongsTo
    {
        return $this->belongsTo(HeroSection::class, 'hero_section_id');
    }

}
