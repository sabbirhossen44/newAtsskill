<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `instructor_request_settings` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     */
    public function up(): void
    {
        Schema::create('instructor_request_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('need_certificate')->default(true);
            $table->boolean('need_identity_scan')->default(true);
            $table->text('bank_information')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_request_settings');
    }
};
