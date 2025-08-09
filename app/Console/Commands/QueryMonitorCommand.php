<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueryMonitorCommand extends Command
{
    protected $signature = 'cas:query-monitor {--duration=30 : Monitor duration in seconds}';
    protected $description = 'Monitor database queries in real-time';

    public function handle()
    {
        $duration = $this->option('duration');
        
        $this->info("CAS Query Monitor (Duration: {$duration}s)");
        $this->info("=" . str_repeat("=", 50));
        
        // Enable query logging
        DB::enableQueryLog();
        
        $startTime = time();
        $queryCount = 0;
        $totalTime = 0;
        
        $this->line("Monitoring queries... Press Ctrl+C to stop early");
        $this->line("");
        
        while ((time() - $startTime) < $duration) {
            // Trigger some sample queries to monitor
            try {
                $start = microtime(true);
                
                // Sample monitoring queries
                $users = DB::table('cas_user.users')->count();
                $clients = DB::table('cas_admin.client_systems')->count();
                $audits = DB::table('cas_audit.audit_logs')
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->count();
                
                $elapsed = (microtime(true) - $start) * 1000;
                $queryCount += 3;
                $totalTime += $elapsed;
                
                $this->line(sprintf(
                    "[%s] Users: %d, Clients: %d, Recent audits: %d (%.2fms)",
                    now()->format('H:i:s'),
                    $users,
                    $clients,
                    $audits,
                    $elapsed
                ));
                
                sleep(2);
                
            } catch (\Exception $e) {
                $this->line("Error: " . $e->getMessage());
            }
        }
        
        // Get query log
        $queries = DB::getQueryLog();
        
        $this->line("");
        $this->info("Query Monitor Summary:");
        $this->info("-" . str_repeat("-", 30));
        $this->line("Duration: {$duration} seconds");
        $this->line("Total queries logged: " . count($queries));
        $this->line("Average queries/second: " . round(count($queries) / $duration, 2));
        $this->line("Average query time: " . round($totalTime / max($queryCount, 1), 2) . "ms");
        
        // Show slowest queries
        if (!empty($queries)) {
            $this->line("");
            $this->info("Recent Queries:");
            foreach (array_slice($queries, -5) as $query) {
                $time = isset($query['time']) ? $query['time'] : 0;
                $sql = substr($query['query'], 0, 80) . '...';
                $this->line("  {$time}ms: {$sql}");
            }
        }
        
        return 0;
    }
}