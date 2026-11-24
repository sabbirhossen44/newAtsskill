<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `instructor_request_setting_translations` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     */
    public function up(): void
    {
        Schema::create('instructor_request_setting_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instructor_request_setting_id');
            $table->string('lang_code');
            $table->string('instructions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_request_setting_translations');
    }
};
