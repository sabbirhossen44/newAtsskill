<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `course_chapter_lessons` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     * Declared foreign keys (most old relations are
     * code-enforced only -- see plan/db-schema.md):
     *   chapter_id -> course_chapters.id
     *   chapter_item_id -> course_chapter_items.id
     *   instructor_id -> users.id
     */
    public function up(): void
    {
        Schema::create('course_chapter_lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('instructor_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('chapter_id');
            $table->unsignedBigInteger('chapter_item_id');
            $table->text('file_path')->nullable();
            $table->enum('storage', ['upload', 'youtube', 'vimeo', 'external_link', 'google_drive', 'iframe', 'aws', 'live'])->nullable()->default('upload');
            $table->string('volume')->nullable();
            $table->string('duration')->nullable();
            $table->enum('file_type', ['video', 'audio', 'pdf', 'txt', 'docx', 'iframe', 'image', 'file', 'live', 'other'])->nullable()->default('video');
            $table->boolean('downloadable')->default(true);
            $table->integer('order')->nullable();
            $table->boolean('is_free')->nullable()->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('instructor_id');
            $table->index('chapter_id');
            $table->index('chapter_item_id');
            $table->foreign('chapter_id')->references('id')->on('course_chapters');
            $table->foreign('chapter_item_id')->references('id')->on('course_chapter_items');
            $table->foreign('instructor_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_chapter_lessons');
    }
};
