<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `course_live_classes` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     * Declared foreign keys (most old relations are
     * code-enforced only -- see plan/db-schema.md):
     *   lesson_id -> course_chapter_lessons.id ON DELETE CASCADE
     */
    public function up(): void
    {
        Schema::create('course_live_classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lesson_id');
            $table->string('start_time')->nullable();
            $table->enum('type', ['zoom', 'jitsi'])->default('zoom');
            $table->string('meeting_id')->nullable();
            $table->string('password')->nullable();
            $table->string('join_url')->nullable();
            $table->timestamps();
            $table->index('lesson_id');
            $table->foreign('lesson_id')->references('id')->on('course_chapter_lessons')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_live_classes');
    }
};
