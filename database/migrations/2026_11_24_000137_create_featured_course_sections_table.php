<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `featured_course_sections` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     */
    public function up(): void
    {
        Schema::create('featured_course_sections', function (Blueprint $table) {
            $table->id();
            $table->integer('all_category')->nullable();
            $table->text('all_category_ids')->nullable();
            $table->boolean('all_category_status')->default(true);
            $table->integer('category_one')->nullable();
            $table->text('category_one_ids')->nullable();
            $table->boolean('category_one_status')->default(true);
            $table->integer('category_two')->nullable();
            $table->text('category_two_ids')->nullable();
            $table->boolean('category_two_status')->default(true);
            $table->integer('category_three')->nullable();
            $table->text('category_three_ids')->nullable();
            $table->boolean('category_three_status')->default(true);
            $table->integer('category_four')->nullable();
            $table->text('category_four_ids')->nullable();
            $table->boolean('category_four_status')->default(true);
            $table->integer('category_five')->nullable();
            $table->text('category_five_ids')->nullable();
            $table->boolean('category_five_status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_course_sections');
    }
};
