<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Create SSO tokens table
     */
    public function up(): void
    {
        Schema::create('cas_user.sso_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('token');
            $table->text('token_hash')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('client_system_id');
            $table->string('user_role');
            $table->json('user_data')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('used_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('cas_user.users')->onDelete('cascade');
            $table->foreign('client_system_id')->references('id')->on('cas_admin.client_systems')->onDelete('cascade');

            $table->index('token');
            $table->index('user_id');
            $table->index('client_system_id');
            $table->index('expires_at');
            $table->index('is_used');
            $table->index('is_active');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        // Drop with CASCADE to handle foreign key dependencies
        DB::statement('DROP TABLE IF EXISTS cas_user.sso_tokens CASCADE');
    }
};
