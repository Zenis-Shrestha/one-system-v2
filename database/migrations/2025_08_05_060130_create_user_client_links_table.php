<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Create user-client system links with encrypted credentials
     */
    public function up(): void
    {
        Schema::create('cas_user.user_client_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('client_system_id');
            $table->string('linked_username')->nullable();
            $table->text('encrypted_password')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('show_in_dashboard')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('cas_user.users')->onDelete('cascade');
            $table->foreign('client_system_id')->references('id')->on('cas_admin.client_systems')->onDelete('cascade');

            $table->index('user_id');
            $table->index('client_system_id');
            $table->index('is_active');
            $table->index('last_used');

            $table->unique(['user_id', 'client_system_id']);
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        // Drop with CASCADE to handle foreign key dependencies
        DB::statement('DROP TABLE IF EXISTS cas_user.user_client_links CASCADE');
    }
};
