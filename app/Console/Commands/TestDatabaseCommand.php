<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDatabaseCommand extends Command
{
    protected $signature = 'cas:test-db';
    protected $description = 'Test CAS database connection and verify schema integrity';

    public function handle()
    {
        $this->info("Testing CAS Database Connection...");
        $this->info("=" . str_repeat("=", 50));
        
        try {
            // Test main connection
            $connection = DB::connection()->getPdo();
            $this->line("✓ Database connection: SUCCESS");
            
            // Test each schema and count records
            $schemas = [
                'cas_user' => ['users', 'user_client_links', 'user_security'],
                'cas_admin' => ['client_systems', 'ip_whitelist', 'sso_settings'],
                'cas_audit' => ['audit_logs']
            ];
            
            $this->info("\nSchema and Table Verification:");
            $this->info("-" . str_repeat("-", 40));
            
            foreach ($schemas as $schema => $tables) {
                $this->line("Schema: {$schema}");
                
                foreach ($tables as $table) {
                    try {
                        $count = $connection->table("{$schema}.{$table}")->count();
                        $this->line("  ✓ {$table}: {$count} records");
                    } catch (\Exception $e) {
                        $this->line("  ✗ {$table}: ERROR - " . $e->getMessage());
                    }
                }
                $this->line("");
            }
            
            // Test database performance
            $this->info("Performance Tests:");
            $this->info("-" . str_repeat("-", 40));
            
            $start = microtime(true);
            $userCount = $connection->table('cas_user.users')->count();
            $queryTime = round((microtime(true) - $start) * 1000, 2);
            $this->line("✓ User count query: {$userCount} users in {$queryTime}ms");
            
            $start = microtime(true);
            $clientCount = $connection->table('cas_admin.client_systems')->count();
            $queryTime = round((microtime(true) - $start) * 1000, 2);
            $this->line("✓ Client systems query: {$clientCount} systems in {$queryTime}ms");
            
            $start = microtime(true);
            $auditCount = $connection->table('cas_audit.audit_logs')->count();
            $queryTime = round((microtime(true) - $start) * 1000, 2);
            $this->line("✓ Audit logs query: {$auditCount} logs in {$queryTime}ms");
            
            // Test recent activity
            $this->info("\nRecent Activity:");
            $this->info("-" . str_repeat("-", 40));
            
            $recentAudits = $connection->table('cas_audit.audit_logs')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
            $this->line("Recent audit logs (7 days): {$recentAudits}");
            
            $activeUsers = $connection->table('cas_user.users')
                ->where('is_active', true)
                ->count();
            $this->line("Active users: {$activeUsers}");
            
            $activeClients = $connection->table('cas_admin.client_systems')
                ->where('is_active', true)
                ->count();
            $this->line("Active client systems: {$activeClients}");
            
            $this->info("\n" . "=" . str_repeat("=", 50));
            $this->info("✓ Database test completed successfully!");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Database test failed: " . $e->getMessage());
            $this->error("Check your database connection and schema configuration.");
            return 1;
        }
    }
}