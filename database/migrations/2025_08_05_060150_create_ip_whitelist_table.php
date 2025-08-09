<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Create IP whitelist table
     */
    public function up(): void
    {
        Schema::create('cas_admin.ip_whitelist', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->string('subnet_mask')->default('full');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('cas_user.users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('cas_user.users')->onDelete('set null');

            $table->index('ip_address');
            $table->index('is_active');
            $table->index('last_used');
            $table->index('expires_at');
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('cas_admin.ip_whitelist');
    }
};
