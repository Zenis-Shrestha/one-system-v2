<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Create comprehensive audit logs table
     */
    public function up(): void
    {
        Schema::create('cas_audit.audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('client_system_id')->nullable();
            $table->string('event_type');
            $table->string('action');
            $table->text('description');
            $table->json('details')->nullable();
            $table->boolean('success')->default(true);
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('cas_user.users')->onDelete('set null');
            $table->foreign('client_system_id')->references('id')->on('cas_admin.client_systems')->onDelete('set null');

            $table->index('user_id');
            $table->index('client_system_id');
            $table->index('event_type');
            $table->index('action');
            $table->index('success');
            $table->index('ip_address');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('cas_audit.audit_logs');
    }
};
