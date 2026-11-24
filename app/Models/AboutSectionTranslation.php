<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutSectionTranslation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function aboutSection(): BelongsTo
    {
        return $this->belongsTo(AboutSection::class, 'about_section_id');
    }

}
