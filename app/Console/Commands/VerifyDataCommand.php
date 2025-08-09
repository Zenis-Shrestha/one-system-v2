<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyDataCommand extends Command
{
    protected $signature = 'cas:verify-data';
    protected $description = 'Verify CAS data integrity and relationships';

    public function handle()
    {
        $this->info("CAS Data Integrity Verification");
        $this->info("=" . str_repeat("=", 50));
        
        $issues = [];
        
        // 1. Check for orphaned records
        $this->info("1. Checking for orphaned records...");
        
        // Orphaned user_client_links
        $orphanedLinks = DB::select("
            SELECT ucl.id 
            FROM cas_user.user_client_links ucl
            LEFT JOIN cas_user.users u ON ucl.user_id = u.id
            LEFT JOIN cas_admin.client_systems cs ON ucl.client_system_id = cs.id
            WHERE u.id IS NULL OR cs.id IS NULL
        ");
        
        if (!empty($orphanedLinks)) {
            $count = count($orphanedLinks);
            $this->line("   ✗ Found {$count} orphaned user-client links");
            $issues[] = "orphaned_user_client_links";
        } else {
            $this->line("   ✓ No orphaned user-client links");
        }
        
        // Orphaned audit logs
        $orphanedAudits = DB::select("
            SELECT al.id 
            FROM cas_audit.audit_logs al
            LEFT JOIN cas_user.users u ON al.user_id = u.id
            LEFT JOIN cas_admin.client_systems cs ON al.client_system_id = cs.id
            WHERE (al.user_id IS NOT NULL AND u.id IS NULL) 
               OR (al.client_system_id IS NOT NULL AND cs.id IS NULL)
        ");
        
        if (!empty($orphanedAudits)) {
            $count = count($orphanedAudits);
            $this->line("   ✗ Found {$count} orphaned audit log entries");
            $issues[] = "orphaned_audit_logs";
        } else {
            $this->line("   ✓ No orphaned audit log entries");
        }
        
        // 2. Check data consistency
        $this->line("");
        $this->info("2. Checking data consistency...");
        
        // Check for duplicate usernames
        $duplicateUsernames = DB::select("
            SELECT username, COUNT(*) as count
            FROM cas_user.users 
            GROUP BY username 
            HAVING COUNT(*) > 1
        ");
        
        if (!empty($duplicateUsernames)) {
            $this->line("   ✗ Found duplicate usernames:");
            foreach ($duplicateUsernames as $dup) {
                $this->line("     - {$dup->username} ({$dup->count} occurrences)");
            }
            $issues[] = "duplicate_usernames";
        } else {
            $this->line("   ✓ No duplicate usernames");
        }
        
        // Check for duplicate emails
        $duplicateEmails = DB::select("
            SELECT email, COUNT(*) as count
            FROM cas_user.users 
            GROUP BY email 
            HAVING COUNT(*) > 1
        ");
        
        if (!empty($duplicateEmails)) {
            $this->line("   ✗ Found duplicate emails:");
            foreach ($duplicateEmails as $dup) {
                $this->line("     - {$dup->email} ({$dup->count} occurrences)");
            }
            $issues[] = "duplicate_emails";
        } else {
            $this->line("   ✓ No duplicate emails");
        }
        
        // 3. Check for missing required data
        $this->line("");
        $this->info("3. Checking required data...");
        
        // Users without passwords
        $usersWithoutPasswords = DB::select("
            SELECT COUNT(*) as count 
            FROM cas_user.users 
            WHERE password IS NULL OR password = ''
        ")[0]->count;
        
        if ($usersWithoutPasswords > 0) {
            $this->line("   ✗ Found {$usersWithoutPasswords} users without passwords");
            $issues[] = "users_without_passwords";
        } else {
            $this->line("   ✓ All users have passwords");
        }
        
        // Client systems without credentials
        $clientsWithoutCreds = DB::select("
            SELECT COUNT(*) as count 
            FROM cas_admin.client_systems 
            WHERE client_id IS NULL OR client_secret IS NULL
        ")[0]->count;
        
        if ($clientsWithoutCreds > 0) {
            $this->line("   ✗ Found {$clientsWithoutCreds} client systems without credentials");
            $issues[] = "clients_without_credentials";
        } else {
            $this->line("   ✓ All client systems have credentials");
        }
        
        // 4. Summary
        $this->line("");
        $this->info("=" . str_repeat("=", 50));
        
        if (empty($issues)) {
            $this->info("✅ DATA INTEGRITY: VERIFIED");
            $this->line("All data integrity checks passed successfully.");
            return 0;
        } else {
            $this->error("❌ DATA INTEGRITY: ISSUES FOUND");
            $this->line("Found " . count($issues) . " data integrity issues:");
            foreach ($issues as $issue) {
                $this->line("  • " . str_replace('_', ' ', ucfirst($issue)));
            }
            $this->line("");
            $this->line("Consider running data repair operations or manual cleanup.");
            return 1;
        }
    }
}