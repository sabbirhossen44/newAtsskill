<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `counter_sections` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     */
    public function up(): void
    {
        Schema::create('counter_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('total_student_count')->nullable();
            $table->integer('total_instructor_count')->nullable();
            $table->integer('total_courses_count')->nullable();
            $table->integer('total_awards_count')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counter_sections');
    }
};
