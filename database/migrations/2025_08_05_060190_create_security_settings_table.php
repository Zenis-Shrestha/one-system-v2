<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Create security settings table
     */
    public function up(): void
    {
        Schema::create('cas_admin.security_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value')->nullable();
            $table->string('setting_type')->default('string');
            $table->text('description')->nullable();
            $table->string('category')->default('general');
            $table->boolean('is_sensitive')->default(false);
            $table->boolean('requires_restart')->default(false);
            $table->timestamps();

            $table->index('setting_key');
            $table->index('setting_type');
            $table->index('category');
            $table->index('is_sensitive');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('cas_admin.security_settings');
    }
};
