<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Central-media pivot for `testimonial` (new in the rebuild; the old
     * dump kept image paths as plain varchar columns).
     * `is_primary` marks the featured image; `order` controls sorting.
     */
    public function up(): void
    {
        Schema::create('testimonial_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('testimonial_id');
            $table->unsignedBigInteger('media_id');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->unique(['testimonial_id', 'media_id']);
            $table->foreign('testimonial_id')->references('id')->on('testimonials')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonial_media');
    }
};
