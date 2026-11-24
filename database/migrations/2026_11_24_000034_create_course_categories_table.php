<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `course_categories` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     * Declared foreign keys (most old relations are
     * code-enforced only -- see plan/db-schema.md):
     *   media_id -> media.id ON DELETE SET NULL
     */
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable();
            $table->integer('order')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedBigInteger('media_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('show_at_trending')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->index('media_id');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_categories');
    }
};
