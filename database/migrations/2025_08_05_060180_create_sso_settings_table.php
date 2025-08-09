<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Create SSO configuration settings table
     */
    public function up(): void
    {
        Schema::create('cas_admin.sso_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value')->nullable();
            $table->string('setting_type')->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_editable')->default(true);
            $table->timestamps();

            $table->index('setting_key');
            $table->index('setting_type');
            $table->index('is_public');
            $table->index('is_editable');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('cas_admin.sso_settings');
    }
};
