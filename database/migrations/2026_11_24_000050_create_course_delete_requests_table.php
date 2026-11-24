<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `course_delete_requests` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     */
    public function up(): void
    {
        Schema::create('course_delete_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->text('message');
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_delete_requests');
    }
};
