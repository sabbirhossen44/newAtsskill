<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorRequestSettingTranslation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function instructorRequestSetting(): BelongsTo
    {
        return $this->belongsTo(InstructorRequestSetting::class, 'instructor_request_setting_id');
    }

}
