<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Create client systems table
     */
    public function up(): void
    {
        Schema::create('cas_admin.client_systems', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('client_id')->unique();
            $table->string('client_secret');
            $table->string('webhook_secret')->nullable();
            $table->text('description')->nullable();
            $table->string('callback_url')->nullable();
            $table->json('allowed_scopes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('credentials_viewed')->default(false);
            $table->timestamp('credentials_viewed_at')->nullable();
            $table->unsignedBigInteger('credentials_viewed_by')->nullable();
            $table->boolean('credentials_shown')->default(false);
            $table->timestamp('credentials_regenerated_at')->nullable();
            $table->unsignedBigInteger('credentials_regenerated_by')->nullable();
            $table->json('server_config')->nullable();
            $table->timestamps();

            $table->index('client_id');
            $table->index('name');
            $table->index('is_active');
            $table->index('credentials_viewed');
            $table->index('credentials_shown');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS cas_admin.client_systems CASCADE');
    }
};
