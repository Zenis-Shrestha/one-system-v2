<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cas_user.sso_tokens', function (Blueprint $table) {
            $table->text('token')->change();
            $table->text('payload')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cas_user.sso_tokens', function (Blueprint $table) {
            $table->string('token', 255)->change();
             $table->string('payload', 1024)->change(); // Assuming previous payload size
        });
    }
};
