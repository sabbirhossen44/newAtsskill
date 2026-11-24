<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `our_features_section_translations` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     */
    public function up(): void
    {
        Schema::create('our_features_section_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('our_features_section_id')->default(1);
            $table->string('lang_code');
            $table->string('title_one')->nullable();
            $table->string('sub_title_one')->nullable();
            $table->string('title_two')->nullable();
            $table->string('sub_title_two')->nullable();
            $table->string('title_three')->nullable();
            $table->string('sub_title_three')->nullable();
            $table->string('title_four')->nullable();
            $table->string('sub_title_four')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('our_features_section_translations');
    }
};
