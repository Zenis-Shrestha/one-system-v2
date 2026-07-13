<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Create unified users table
     */
    public function up(): void
    {
        Schema::create('cas_user.users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->json('preferences')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            // EncryptedArrayFallback stores ciphertext, which requires TEXT.
            $table->text('two_factor_backup_codes')->nullable();
            $table->timestamps();

            $table->index('username');
            $table->index('email');
            $table->index('role');
            $table->index('is_active');
            $table->index('last_login');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS cas_user.users CASCADE');
    }
};
