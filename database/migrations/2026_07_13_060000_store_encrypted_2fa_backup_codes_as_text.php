<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Encrypted backup codes are Laravel ciphertext, not JSON documents.
     * Preserve existing JSON arrays as text; the fallback cast can still read
     * them and encrypts them on their next write.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE cas_user.users ALTER COLUMN two_factor_backup_codes TYPE TEXT USING two_factor_backup_codes::text');
        DB::statement('ALTER TABLE cas_user.user_security ALTER COLUMN two_factor_backup_codes TYPE TEXT USING two_factor_backup_codes::text');
    }

    /**
     * This security migration is intentionally irreversible because encrypted
     * ciphertext cannot safely be represented as a PostgreSQL JSON document.
     */
    public function down(): void
    {
        // Intentionally left blank.
    }
};
