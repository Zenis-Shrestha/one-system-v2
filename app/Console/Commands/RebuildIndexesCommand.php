<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildIndexesCommand extends Command
{
    protected $signature = 'cas:rebuild-indexes {--force : Skip confirmation prompt}';
    protected $description = 'Rebuild database indexes for optimal performance';

    public function handle()
    {
        $this->info("CAS Index Rebuild Utility");
        $this->info("=" . str_repeat("=", 50));
        
        $force = $this->option('force');
        
        if (!$force) {
            $this->warn("This operation will rebuild all indexes and may take several minutes.");
            if (!$this->confirm('Continue with index rebuild?')) {
                $this->line('Operation cancelled.');
                return 0;
            }
        }
        
        try {
            // Get all indexes for CAS schemas
            $indexes = DB::select("
                SELECT 
                    schemaname,
                    tablename,
                    indexname,
                    indexdef
                FROM pg_indexes 
                WHERE schemaname IN ('cas_user', 'cas_admin', 'cas_audit')
                AND indexname NOT LIKE '%_pkey'  -- Skip primary keys
                ORDER BY schemaname, tablename, indexname
            ");
            
            $this->line("Found " . count($indexes) . " indexes to rebuild");
            $this->line("");
            
            // Show current index status
            $this->info("Current Index Statistics:");
            $indexStats = DB::select("
                SELECT 
                    schemaname,
                    tablename,
                    indexname,
                    idx_scan,
                    idx_tup_read,
                    idx_tup_fetch
                FROM pg_stat_user_indexes 
                WHERE schemaname IN ('cas_user', 'cas_admin', 'cas_audit')
                ORDER BY idx_scan DESC
            ");
            
            foreach ($indexStats as $stat) {
                $usage = $stat->idx_scan > 0 ? "Used {$stat->idx_scan} times" : "Never used";
                $this->line("  {$stat->schemaname}.{$stat->indexname}: {$usage}");
            }
            
            $this->line("");
            
            // Rebuild indexes
            $this->info("Rebuilding Indexes:");
            $rebuiltCount = 0;
            $errors = [];
            
            foreach ($indexes as $index) {
                try {
                    $this->line("  Rebuilding {$index->schemaname}.{$index->indexname}...");
                    
                    // REINDEX command
                    DB::statement("REINDEX INDEX \"{$index->schemaname}\".\"{$index->indexname}\"");
                    
                    $rebuiltCount++;
                } catch (\Exception $e) {
                    $error = "Failed to rebuild {$index->schemaname}.{$index->indexname}: " . $e->getMessage();
                    $this->line("    ✗ {$error}");
                    $errors[] = $error;
                }
            }
            
            $this->line("");
            
            // Create missing recommended indexes
            $this->info("Checking for Missing Recommended Indexes:");
            
            $recommendedIndexes = [
                'cas_user.users' => [
                    ['columns' => ['email'], 'name' => 'idx_users_email'],
                    ['columns' => ['username'], 'name' => 'idx_users_username'],
                    ['columns' => ['is_active'], 'name' => 'idx_users_active'],
                ],
                'cas_admin.client_systems' => [
                    ['columns' => ['client_id'], 'name' => 'idx_client_systems_client_id'],
                    ['columns' => ['is_active'], 'name' => 'idx_client_systems_active'],
                    ['columns' => ['domain'], 'name' => 'idx_client_systems_domain'],
                ],
                'cas_audit.audit_logs' => [
                    ['columns' => ['user_id'], 'name' => 'idx_audit_logs_user_id'],
                    ['columns' => ['client_system_id'], 'name' => 'idx_audit_logs_client_system_id'],
                    ['columns' => ['created_at'], 'name' => 'idx_audit_logs_created_at'],
                    ['columns' => ['event_type'], 'name' => 'idx_audit_logs_event_type'],
                ],
                'cas_user.user_client_links' => [
                    ['columns' => ['user_id'], 'name' => 'idx_user_client_links_user_id'],
                    ['columns' => ['client_system_id'], 'name' => 'idx_user_client_links_client_system_id'],
                ]
            ];
            
            $createdIndexes = 0;
            foreach ($recommendedIndexes as $table => $tableIndexes) {
                foreach ($tableIndexes as $indexDef) {
                    $indexName = $indexDef['name'];
                    $columns = implode(', ', array_map(fn($col) => "\"{$col}\"", $indexDef['columns']));
                    
                    // Check if index exists
                    $exists = DB::select("
                        SELECT 1 FROM pg_indexes 
                        WHERE schemaname = ? AND tablename = ? AND indexname = ?
                    ", [explode('.', $table)[0], explode('.', $table)[1], $indexName]);
                    
                    if (empty($exists)) {
                        try {
                            $this->line("  Creating missing index: {$indexName}");
                            DB::statement("CREATE INDEX \"{$indexName}\" ON \"{$table}\" ({$columns})");
                            $createdIndexes++;
                        } catch (\Exception $e) {
                            $this->line("    ✗ Failed to create {$indexName}: " . $e->getMessage());
                            $errors[] = "Failed to create index {$indexName}";
                        }
                    }
                }
            }
            
            // Final statistics
            $this->line("");
            $this->info("Index Rebuild Summary:");
            $this->info("-" . str_repeat("-", 30));
            $this->line("Indexes rebuilt: {$rebuiltCount}");
            $this->line("New indexes created: {$createdIndexes}");
            $this->line("Errors encountered: " . count($errors));
            
            if (!empty($errors)) {
                $this->line("");
                $this->warn("Errors:");
                foreach ($errors as $error) {
                    $this->line("  • {$error}");
                }
            }
            
            $this->line("");
            if (empty($errors)) {
                $this->info("✓ Index rebuild completed successfully!");
            } else {
                $this->warn("Index rebuild completed with " . count($errors) . " errors.");
            }
            
            return empty($errors) ? 0 : 1;
            
        } catch (\Exception $e) {
            $this->error("Index rebuild failed: " . $e->getMessage());
            return 1;
        }
    }
}