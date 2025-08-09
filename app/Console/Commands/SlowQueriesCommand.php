<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SlowQueriesCommand extends Command
{
    protected $signature = 'cas:slow-queries {--threshold=100 : Slow query threshold in milliseconds}';
    protected $description = 'Analyze slow database queries';

    public function handle()
    {
        $threshold = $this->option('threshold');
        
        $this->info("CAS Slow Query Analysis");
        $this->info("=" . str_repeat("=", 50));
        $this->line("Threshold: {$threshold}ms");
        $this->line("");
        
        try {
            // Get current PostgreSQL slow query settings
            $slowQueryLog = DB::select("
                SELECT name, setting, unit 
                FROM pg_settings 
                WHERE name IN ('log_min_duration_statement', 'log_statement', 'log_duration')
            ");
            
            $this->info("PostgreSQL Query Logging Settings:");
            foreach ($slowQueryLog as $setting) {
                $value = $setting->setting;
                if ($setting->unit) $value .= $setting->unit;
                $this->line("  {$setting->name}: {$value}");
            }
            
            $this->line("");
            
            // Analyze current database statistics
            $this->info("Query Performance Statistics:");
            
            $queryStats = DB::select("
                SELECT 
                    schemaname,
                    tablename,
                    seq_scan,
                    seq_tup_read,
                    idx_scan,
                    idx_tup_fetch,
                    n_tup_ins,
                    n_tup_upd,
                    n_tup_del
                FROM pg_stat_user_tables 
                WHERE schemaname IN ('cas_user', 'cas_admin', 'cas_audit')
                ORDER BY seq_tup_read DESC
            ");
            
            foreach ($queryStats as $stat) {
                $seqScanRatio = $stat->seq_scan > 0 ? round(($stat->seq_tup_read / $stat->seq_scan), 2) : 0;
                $idxScanRatio = $stat->idx_scan > 0 ? round(($stat->idx_tup_fetch / $stat->idx_scan), 2) : 0;
                
                $this->line("Table: {$stat->schemaname}.{$stat->tablename}");
                $this->line("  Sequential scans: {$stat->seq_scan} (avg {$seqScanRatio} rows/scan)");
                $this->line("  Index scans: {$stat->idx_scan} (avg {$idxScanRatio} rows/scan)");
                
                if ($seqScanRatio > 1000) {
                    $this->line("  ⚠️ High sequential scan ratio - consider adding indexes");
                }
                $this->line("");
            }
            
            // Run performance tests on common queries
            $this->info("Common Query Performance Tests:");
            
            $testQueries = [
                'User lookup by email' => [
                    'sql' => "SELECT * FROM cas_user.users WHERE email = ?",
                    'params' => ['admin@cas-system.com']
                ],
                'Client system lookup' => [
                    'sql' => "SELECT * FROM cas_admin.client_systems WHERE is_active = ?",
                    'params' => [true]
                ],
                'Recent audit logs' => [
                    'sql' => "SELECT * FROM cas_audit.audit_logs WHERE created_at >= ? ORDER BY created_at DESC LIMIT 10",
                    'params' => [now()->subDays(7)]
                ],
                'User-client links join' => [
                    'sql' => "SELECT u.username, cs.name FROM cas_user.users u JOIN cas_user.user_client_links ucl ON u.id = ucl.user_id JOIN cas_admin.client_systems cs ON ucl.client_system_id = cs.id",
                    'params' => []
                ]
            ];
            
            foreach ($testQueries as $name => $query) {
                $times = [];
                for ($i = 0; $i < 3; $i++) {
                    $start = microtime(true);
                    DB::select($query['sql'], $query['params']);
                    $times[] = (microtime(true) - $start) * 1000;
                }
                
                $avgTime = round(array_sum($times) / count($times), 2);
                $maxTime = round(max($times), 2);
                
                $status = $avgTime > $threshold ? "SLOW" : "OK";
                $this->line("  {$name}: {$avgTime}ms avg, {$maxTime}ms max [{$status}]");
            }
            
            // Index recommendations
            $this->line("");
            $this->info("Index Analysis:");
            
            $indexes = DB::select("
                SELECT 
                    schemaname,
                    tablename,
                    indexname,
                    indexdef
                FROM pg_indexes 
                WHERE schemaname IN ('cas_user', 'cas_admin', 'cas_audit')
                ORDER BY schemaname, tablename
            ");
            
            $indexCount = count($indexes);
            $this->line("Current indexes: {$indexCount}");
            
            // Recommendations
            $this->line("");
            $this->info("Optimization Recommendations:");
            
            $recommendations = [];
            
            foreach ($queryStats as $stat) {
                if ($stat->seq_scan > 100 && $stat->seq_tup_read / max($stat->seq_scan, 1) > 100) {
                    $recommendations[] = "Consider adding indexes to {$stat->schemaname}.{$stat->tablename} (high sequential scan ratio)";
                }
            }
            
            if (empty($recommendations)) {
                $this->line("  ✓ No immediate optimization recommendations");
            } else {
                foreach ($recommendations as $rec) {
                    $this->line("  • {$rec}");
                }
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error analyzing slow queries: " . $e->getMessage());
            return 1;
        }
    }
}