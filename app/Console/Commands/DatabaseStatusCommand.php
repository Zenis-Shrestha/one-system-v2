<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseStatusCommand extends Command
{
    protected $signature = 'cas:db-status';
    protected $description = 'Check detailed database connection status and performance metrics';

    public function handle()
    {
        $this->info("CAS Database Status Report");
        $this->info("=" . str_repeat("=", 50));

        try {
            $connection = DB::connection('cas_system');
            $pdo = $connection->getPdo();

            // Basic connection info
            $this->info("Connection Details:");
            $this->line("✓ Database: Connected successfully");
            $this->line("Connection: cas_system");
            $this->line("Driver: " . $connection->getDriverName());
            $this->line("Database: " . $connection->getDatabaseName());

            // Get PostgreSQL version and status
            $version = DB::select('SELECT version() as version')[0];
            $this->line("Version: " . explode(',', $version->version)[0]);

            // Connection pool info
            $stats = DB::select("
                SELECT
                    count(*) as total_connections,
                    count(*) FILTER (WHERE state = 'active') as active_connections,
                    count(*) FILTER (WHERE state = 'idle') as idle_connections
                FROM pg_stat_activity
                WHERE datname = current_database()
            ")[0];

            $this->line("");
            $this->info("Connection Pool:");
            $this->line("Total connections: {$stats->total_connections}");
            $this->line("Active connections: {$stats->active_connections}");
            $this->line("Idle connections: {$stats->idle_connections}");

            // Database size info
            $dbSize = DB::select("
                SELECT pg_size_pretty(pg_database_size(current_database())) as size
            ")[0];

            $this->line("");
            $this->info("Database Metrics:");
            $this->line("Database size: {$dbSize->size}");

            // Schema and table info
            $schemas = ['cas_user', 'cas_admin', 'cas_audit'];
            foreach ($schemas as $schema) {
                $tables = DB::select("
                    SELECT
                        schemaname,
                        tablename,
                        pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as size
                    FROM pg_tables
                    WHERE schemaname = ?
                    ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC
                ", [$schema]);

                if (!empty($tables)) {
                    $this->line("");
                    $this->info("Schema: {$schema}");
                    foreach ($tables as $table) {
                        $this->line("  {$table->tablename}: {$table->size}");
                    }
                }
            }

            // Recent activity
            $this->line("");
            $this->info("Recent Activity (24 hours):");

            $activity = [
                'users' => DB::table('cas_user.users')->where('created_at', '>=', now()->subDay())->count(),
                'audit_logs' => DB::table('cas_audit.audit_logs')->where('created_at', '>=', now()->subDay())->count(),
                'client_systems' => DB::table('cas_admin.client_systems')->where('updated_at', '>=', now()->subDay())->count(),
            ];

            foreach ($activity as $type => $count) {
                $this->line("{$type}: {$count}");
            }

            // Performance test
            $this->line("");
            $this->info("Performance Test:");
            $start = microtime(true);
            DB::select('SELECT 1 as test');
            $queryTime = round((microtime(true) - $start) * 1000, 2);
            $this->line("Simple query: {$queryTime}ms");

            $this->line("");
            $this->info("✓ Database is healthy and responsive");

            return 0;

        } catch (\Exception $e) {
            $this->error("Database status check failed: " . $e->getMessage());
            return 1;
        }
    }
}
