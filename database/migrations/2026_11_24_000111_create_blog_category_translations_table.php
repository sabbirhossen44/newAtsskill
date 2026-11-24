<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `blog_category_translations` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     * Declared foreign keys (most old relations are
     * code-enforced only -- see plan/db-schema.md):
     *   blog_category_id -> blog_categories.id
     */
    public function up(): void
    {
        Schema::create('blog_category_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blog_category_id');
            $table->string('lang_code');
            $table->string('title');
            $table->string('short_description')->nullable();
            $table->timestamps();
            $table->index('blog_category_id');
            $table->foreign('blog_category_id')->references('id')->on('blog_categories');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_category_translations');
    }
};
