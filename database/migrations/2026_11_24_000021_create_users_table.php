<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create `users` exactly as in the old `atsskill1` dump.
     * Table/column names are kept verbatim; no column is renamed.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->unsignedBigInteger('media_id')->nullable();
            $table->enum('role', ['instructor', 'student'])->default('student');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->string('status')->default('active');
            $table->string('is_banned')->default('no');
            $table->string('verification_token')->nullable();
            $table->string('forget_password_token')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('image')->default('/uploads/website-images/frontend-avatar.png');
            $table->string('cover')->default('/uploads/website-images/frontend-cover.png');
            $table->decimal('wallet_balance', 8, 2)->default(0.00);
            $table->text('bio')->nullable();
            $table->text('short_bio')->nullable();
            $table->string('job_title')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->integer('age')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('website')->nullable();
            $table->string('github')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
