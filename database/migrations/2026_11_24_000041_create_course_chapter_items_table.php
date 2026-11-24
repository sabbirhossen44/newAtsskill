<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `course_chapter_items` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     * Declared foreign keys (most old relations are
     * code-enforced only -- see plan/db-schema.md):
     *   chapter_id -> course_chapters.id
     *   instructor_id -> users.id
     */
    public function up(): void
    {
        Schema::create('course_chapter_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instructor_id');
            $table->unsignedBigInteger('chapter_id');
            $table->enum('type', ['lesson', 'quiz', 'document', 'live'])->nullable()->default('lesson');
            $table->integer('order');
            $table->timestamps();
            $table->index('instructor_id');
            $table->index('chapter_id');
            $table->foreign('chapter_id')->references('id')->on('course_chapters');
            $table->foreign('instructor_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_chapter_items');
    }
};
