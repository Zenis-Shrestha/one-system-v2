<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Create PostgreSQL schemas
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS cas_admin;');
        DB::statement('CREATE SCHEMA IF NOT EXISTS cas_user;');
        DB::statement('CREATE SCHEMA IF NOT EXISTS cas_public;');
        DB::statement('CREATE SCHEMA IF NOT EXISTS cas_audit;');
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        DB::statement('DROP SCHEMA IF EXISTS cas_audit CASCADE;');
        DB::statement('DROP SCHEMA IF EXISTS cas_public CASCADE;');
        DB::statement('DROP SCHEMA IF EXISTS cas_user CASCADE;');
        DB::statement('DROP SCHEMA IF EXISTS cas_admin CASCADE;');
    }
};
