<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseLiveClass extends Model
{
    use HasFactory;

    protected $guarded = ['id'];


    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseChapterLesson::class, 'lesson_id', 'id');
    }

}
