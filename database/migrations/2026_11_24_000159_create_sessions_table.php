<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sessions table, taken from the stock Laravel 13 scaffold migration
     * (0001_01_01_000000_create_users_table). The stock file itself was NOT
     * brought in: it also creates `users` and `password_reset_tokens`,
     * which Phase 1 already provides (2026_11_24_000021 / 000150).
     * Phase 1 has no sessions table, so only this table is created here.
     */
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
