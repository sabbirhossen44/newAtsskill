<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `course_progress` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     */
    public function up(): void
    {
        Schema::create('course_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('chapter_id')->nullable();
            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->boolean('watched')->default(false);
            $table->boolean('current')->default(false);
            $table->enum('type', ['lesson', 'quiz', 'document', 'live'])->default('lesson');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_progress');
    }
};
