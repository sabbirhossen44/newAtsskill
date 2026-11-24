<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaqSectionTranslation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function faqSection(): BelongsTo
    {
        return $this->belongsTo(FaqSection::class, 'faq_section_id');
    }

}
