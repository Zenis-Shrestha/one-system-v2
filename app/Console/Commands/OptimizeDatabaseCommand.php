<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OptimizeDatabaseCommand extends Command
{
    protected $signature = 'cas:optimize-db {--analyze : Run ANALYZE on all tables} {--vacuum : Run VACUUM on all tables}';
    protected $description = 'Optimize CAS database performance';

    public function handle()
    {
        $this->info("CAS Database Optimization");
        $this->info("=" . str_repeat("=", 50));
        
        $runAnalyze = $this->option('analyze');
        $runVacuum = $this->option('vacuum');
        
        if (!$runAnalyze && !$runVacuum) {
            $runAnalyze = $this->confirm('Run ANALYZE to update table statistics?', true);
            $runVacuum = $this->confirm('Run VACUUM to clean up dead rows?', true);
        }
        
        try {
            // Get all CAS tables
            $tables = DB::select("
                SELECT schemaname, tablename 
                FROM pg_tables 
                WHERE schemaname IN ('cas_user', 'cas_admin', 'cas_audit')
                ORDER BY schemaname, tablename
            ");
            
            $this->line("Found " . count($tables) . " tables to optimize");
            $this->line("");
            
            // Before optimization stats
            $this->info("Pre-optimization Statistics:");
            $totalSize = 0;
            foreach ($tables as $table) {
                $size = DB::select("
                    SELECT pg_size_pretty(pg_total_relation_size('{$table->schemaname}.{$table->tablename}')) as size,
                           pg_total_relation_size('{$table->schemaname}.{$table->tablename}') as bytes
                ")[0];
                
                $this->line("  {$table->schemaname}.{$table->tablename}: {$size->size}");
                $totalSize += $size->bytes;
            }
            
            $totalSizePretty = DB::select("SELECT pg_size_pretty({$totalSize}) as size")[0]->size;
            $this->line("Total size: {$totalSizePretty}");
            $this->line("");
            
            // Run ANALYZE if requested
            if ($runAnalyze) {
                $this->info("Running ANALYZE...");
                foreach ($tables as $table) {
                    $this->line("  Analyzing {$table->schemaname}.{$table->tablename}");
                    DB::statement("ANALYZE \"{$table->schemaname}\".\"{$table->tablename}\"");
                }
                $this->line("✓ ANALYZE completed");
                $this->line("");
            }
            
            // Run VACUUM if requested
            if ($runVacuum) {
                $this->info("Running VACUUM...");
                foreach ($tables as $table) {
                    $this->line("  Vacuuming {$table->schemaname}.{$table->tablename}");
                    DB::statement("VACUUM \"{$table->schemaname}\".\"{$table->tablename}\"");
                }
                $this->line("✓ VACUUM completed");
                $this->line("");
            }
            
            // After optimization stats
            if ($runAnalyze || $runVacuum) {
                $this->info("Post-optimization Statistics:");
                $newTotalSize = 0;
                foreach ($tables as $table) {
                    $size = DB::select("
                        SELECT pg_size_pretty(pg_total_relation_size('{$table->schemaname}.{$table->tablename}')) as size,
                               pg_total_relation_size('{$table->schemaname}.{$table->tablename}') as bytes
                    ")[0];
                    
                    $this->line("  {$table->schemaname}.{$table->tablename}: {$size->size}");
                    $newTotalSize += $size->bytes;
                }
                
                $newTotalSizePretty = DB::select("SELECT pg_size_pretty({$newTotalSize}) as size")[0]->size;
                $savedBytes = $totalSize - $newTotalSize;
                $savedPretty = $savedBytes > 0 ? DB::select("SELECT pg_size_pretty({$savedBytes}) as size")[0]->size : '0 bytes';
                
                $this->line("New total size: {$newTotalSizePretty}");
                $this->line("Space reclaimed: {$savedPretty}");
            }
            
            // Additional optimizations
            $this->line("");
            $this->info("Additional Optimization Checks:");
            
            // Check for bloated tables
            $bloatInfo = DB::select("
                SELECT 
                    schemaname,
                    tablename,
                    n_dead_tup,
                    n_live_tup,
                    CASE WHEN n_live_tup > 0 
                         THEN round(100.0 * n_dead_tup / (n_live_tup + n_dead_tup), 2) 
                         ELSE 0 
                    END as dead_tuple_percent
                FROM pg_stat_user_tables 
                WHERE schemaname IN ('cas_user', 'cas_admin', 'cas_audit')
                ORDER BY dead_tuple_percent DESC
            ");
            
            foreach ($bloatInfo as $bloat) {
                if ($bloat->dead_tuple_percent > 10) {
                    $this->line("  ⚠️ {$bloat->schemaname}.{$bloat->tablename}: {$bloat->dead_tuple_percent}% dead tuples");
                } else {
                    $this->line("  ✓ {$bloat->schemaname}.{$bloat->tablename}: {$bloat->dead_tuple_percent}% dead tuples");
                }
            }
            
            $this->line("");
            $this->info("✓ Database optimization completed successfully!");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Database optimization failed: " . $e->getMessage());
            return 1;
        }
    }
}