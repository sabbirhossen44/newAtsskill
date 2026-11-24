<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `migrations` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     */
    public function up(): void
    {
        // `migrations` is owned by Laravel's migrator itself;
        // only create it when it does not already exist.
        if (Schema::hasTable('migrations')) {
            return;
        }
        Schema::create('migrations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('migration');
            $table->integer('batch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('migrations');
    }
};
