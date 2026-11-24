<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `jitsi_settings` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     * Declared foreign keys (most old relations are
     * code-enforced only -- see plan/db-schema.md):
     *   instructor_id -> users.id ON DELETE CASCADE
     */
    public function up(): void
    {
        Schema::create('jitsi_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instructor_id');
            $table->string('api_key');
            $table->string('app_id');
            $table->text('permissions')->nullable();
            $table->timestamps();
            $table->index('instructor_id');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jitsi_settings');
    }
};
