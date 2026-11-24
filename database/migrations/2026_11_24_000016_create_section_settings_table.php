<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `section_settings` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     */
    public function up(): void
    {
        Schema::create('section_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('hero_section')->default(false);
            $table->boolean('top_category_section')->default(false);
            $table->boolean('brands_section')->default(false);
            $table->boolean('about_section')->default(false);
            $table->boolean('featured_course_section')->default(false);
            $table->boolean('news_letter_section')->default(false);
            $table->boolean('featured_instructor_section')->default(false);
            $table->boolean('counter_section')->default(false);
            $table->boolean('faq_section')->default(false);
            $table->boolean('our_features_section')->default(false);
            $table->boolean('testimonial_section')->default(false);
            $table->boolean('banner_section')->default(false);
            $table->boolean('latest_blog_section')->default(false);
            $table->boolean('blog_page')->default(false);
            $table->boolean('about_page')->default(false);
            $table->boolean('contact_page')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_settings');
    }
};
