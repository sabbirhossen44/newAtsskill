<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounterSectionTranslation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function counterSection(): BelongsTo
    {
        return $this->belongsTo(CounterSection::class, 'counter_section_id');
    }

}
