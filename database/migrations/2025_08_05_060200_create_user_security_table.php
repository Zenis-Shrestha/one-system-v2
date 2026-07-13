<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Create user-specific security settings table
     */
    public function up(): void
    {
        Schema::create('cas_user.user_security', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();
            // EncryptedArrayFallback stores ciphertext, which requires TEXT.
            $table->text('two_factor_backup_codes')->nullable();
            $table->timestamp('two_factor_setup_at')->nullable();
            $table->boolean('password_reset_required')->default(false);
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->json('security_preferences')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('cas_user.users')->onDelete('cascade');

            $table->index('user_id');
            $table->index('two_factor_enabled');
            $table->index('password_reset_required');
            $table->index('failed_login_attempts');
            $table->index('locked_until');

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('cas_user.user_security');
    }
};
