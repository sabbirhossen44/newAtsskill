<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomPageTranslation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function customPage(): BelongsTo
    {
        return $this->belongsTo(CustomPage::class, 'custom_page_id');
    }

}
