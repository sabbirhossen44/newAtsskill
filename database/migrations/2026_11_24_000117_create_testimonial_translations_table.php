<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `testimonial_translations` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     */
    public function up(): void
    {
        Schema::create('testimonial_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('testimonial_id');
            $table->string('lang_code');
            $table->string('name');
            $table->string('designation');
            $table->text('comment');
            $table->timestamps();
            $table->index('lang_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonial_translations');
    }
};
