<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PerformanceCheckCommand extends Command
{
    protected $signature = 'cas:performance-check';
    protected $description = 'Analyze CAS system performance and identify bottlenecks';

    public function handle()
    {
        $this->info("CAS Performance Analysis");
        $this->info("=" . str_repeat("=", 50));
        
        // 1. Database Performance
        $this->info("1. Database Performance:");
        
        $queries = [
            'Simple SELECT' => 'SELECT 1 as test',
            'User count' => 'SELECT COUNT(*) FROM cas_user.users',
            'Client count' => 'SELECT COUNT(*) FROM cas_admin.client_systems',
            'Recent audits' => 'SELECT COUNT(*) FROM cas_audit.audit_logs WHERE created_at >= NOW() - INTERVAL \'24 hours\'',
            'Complex join' => 'SELECT u.username, COUNT(ucl.id) as links FROM cas_user.users u LEFT JOIN cas_user.user_client_links ucl ON u.id = ucl.user_id GROUP BY u.id, u.username'
        ];
        
        foreach ($queries as $name => $sql) {
            $times = [];
            for ($i = 0; $i < 3; $i++) {
                $start = microtime(true);
                DB::select($sql);
                $times[] = (microtime(true) - $start) * 1000;
            }
            $avgTime = round(array_sum($times) / count($times), 2);
            $this->line("   {$name}: {$avgTime}ms");
        }
        
        // 2. Cache Performance
        $this->line("");
        $this->info("2. Cache Performance:");
        
        $cacheOps = [
            'Write (small)' => function() {
                $start = microtime(true);
                Cache::put('perf_test_small', 'test_data', 60);
                return (microtime(true) - $start) * 1000;
            },
            'Read (small)' => function() {
                $start = microtime(true);
                Cache::get('perf_test_small');
                return (microtime(true) - $start) * 1000;
            },
            'Write (large)' => function() {
                $largeData = str_repeat('x', 10000);
                $start = microtime(true);
                Cache::put('perf_test_large', $largeData, 60);
                return (microtime(true) - $start) * 1000;
            },
            'Read (large)' => function() {
                $start = microtime(true);
                Cache::get('perf_test_large');
                return (microtime(true) - $start) * 1000;
            }
        ];
        
        foreach ($cacheOps as $name => $operation) {
            $time = round($operation(), 2);
            $this->line("   {$name}: {$time}ms");
        }
        
        // Cleanup
        Cache::forget('perf_test_small');
        Cache::forget('perf_test_large');
        
        // 3. Memory Usage
        $this->line("");
        $this->info("3. Memory Analysis:");
        $this->line("   Current usage: " . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB");
        $this->line("   Peak usage: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB");
        $this->line("   Memory limit: " . ini_get('memory_limit'));
        
        // 4. Database Statistics
        $this->line("");
        $this->info("4. Database Statistics:");
        
        $stats = DB::select("
            SELECT 
                schemaname,
                tablename,
                pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as size,
                n_tup_ins as inserts,
                n_tup_upd as updates,
                n_tup_del as deletes
            FROM pg_stat_user_tables 
            WHERE schemaname IN ('cas_user', 'cas_admin', 'cas_audit')
            ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC
        ");
        
        foreach ($stats as $stat) {
            $this->line("   {$stat->schemaname}.{$stat->tablename}: {$stat->size} (I:{$stat->inserts} U:{$stat->updates} D:{$stat->deletes})");
        }
        
        // 5. Recommendations
        $this->line("");
        $this->info("5. Performance Recommendations:");
        
        $recommendations = [];
        
        // Check for slow queries
        $slowQueries = array_filter($times, fn($time) => $time > 100);
        if (!empty($slowQueries)) {
            $recommendations[] = "Consider database indexing - some queries exceed 100ms";
        }
        
        // Check memory usage
        $memoryUsageMB = memory_get_usage(true) / 1024 / 1024;
        if ($memoryUsageMB > 50) {
            $recommendations[] = "High memory usage detected - consider optimization";
        }
        
        // Check audit log size
        $auditCount = DB::table('cas_audit.audit_logs')->count();
        if ($auditCount > 10000) {
            $recommendations[] = "Consider audit log cleanup - {$auditCount} records found";
        }
        
        if (empty($recommendations)) {
            $this->line("   ✓ Performance looks good - no immediate concerns");
        } else {
            foreach ($recommendations as $rec) {
                $this->line("   • {$rec}");
            }
        }
        
        return 0;
    }
}