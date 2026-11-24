<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `quizzes` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     */
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chapter_item_id');
            $table->unsignedBigInteger('instructor_id');
            $table->unsignedBigInteger('chapter_id');
            $table->unsignedBigInteger('course_id');
            $table->string('title');
            $table->string('time')->nullable();
            $table->string('attempt')->nullable();
            $table->string('pass_mark')->nullable();
            $table->string('total_mark')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
