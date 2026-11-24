<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'hero_section' => 'boolean',
            'top_category_section' => 'boolean',
            'brands_section' => 'boolean',
            'about_section' => 'boolean',
            'featured_course_section' => 'boolean',
            'news_letter_section' => 'boolean',
            'featured_instructor_section' => 'boolean',
            'counter_section' => 'boolean',
            'faq_section' => 'boolean',
            'our_features_section' => 'boolean',
            'testimonial_section' => 'boolean',
            'banner_section' => 'boolean',
            'latest_blog_section' => 'boolean',
            'blog_page' => 'boolean',
            'about_page' => 'boolean',
            'contact_page' => 'boolean',
        ];
    }

}
