<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturedCourseSection extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'all_category_status' => 'boolean',
            'category_one_status' => 'boolean',
            'category_two_status' => 'boolean',
            'category_three_status' => 'boolean',
            'category_four_status' => 'boolean',
            'category_five_status' => 'boolean',
        ];
    }

}
