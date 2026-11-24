<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedInstructorTranslation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function featuredInstructor(): BelongsTo
    {
        return $this->belongsTo(FeaturedInstructor::class, 'featured_instructor_section_id');
    }

}
