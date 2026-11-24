<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `menu_translations` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     * Declared foreign keys (most old relations are
     * code-enforced only -- see plan/db-schema.md):
     *   menu_id -> menus.id ON DELETE CASCADE
     */
    public function up(): void
    {
        Schema::create('menu_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->string('lang_code');
            $table->string('name');
            $table->timestamps();
            $table->index('menu_id');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_translations');
    }
};
