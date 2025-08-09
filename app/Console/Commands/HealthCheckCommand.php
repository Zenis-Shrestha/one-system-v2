<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthCheckCommand extends Command
{
    protected $signature = 'cas:health-check';
    protected $description = 'Comprehensive CAS system health check';

    public function handle()
    {
        $this->info("CAS System Health Check");
        $this->info("=" . str_repeat("=", 50));

        $overallHealth = true;

        // 1. Database Check
        $this->info("1. Database Health:");
        try {
            $connection = DB::connection('cas_system');
            $connection->getPdo();
            $this->line("   ✓ Database connection: OK");

            // Test each schema
            $schemas = ['cas_user', 'cas_admin', 'cas_audit'];
            foreach ($schemas as $schema) {
                $testQuery = $connection->select("SELECT 1 FROM information_schema.schemata WHERE schema_name = ?", [$schema]);
                if (empty($testQuery)) {
                    $this->line("   ✗ Schema {$schema}: MISSING");
                    $overallHealth = false;
                } else {
                    $this->line("   ✓ Schema {$schema}: OK");
                }
            }
        } catch (\Exception $e) {
            $this->line("   ✗ Database: ERROR - " . $e->getMessage());
            $overallHealth = false;
        }

        // 2. Cache Check
        $this->line("");
        $this->info("2. Cache System:");
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 60);
            $value = Cache::get($testKey);
            Cache::forget($testKey);

            if ($value === 'test') {
                $this->line("   ✓ Cache read/write: OK");
            } else {
                $this->line("   ✗ Cache read/write: FAILED");
                $overallHealth = false;
            }
        } catch (\Exception $e) {
            $this->line("   ✗ Cache system: ERROR - " . $e->getMessage());
            $overallHealth = false;
        }

        // 3. Configuration Check
        $this->line("");
        $this->info("3. Configuration:");

        $configs = [
            'JWT_SECRET' => env('JWT_SECRET'),
            'DATABASE_URL' => env('DATABASE_URL') ? 'Set' : null,
            'APP_ENV' => env('APP_ENV'),
            'APP_DEBUG' => env('APP_DEBUG') ? 'true' : 'false'
        ];

        foreach ($configs as $key => $value) {
            if ($value) {
                $this->line("   ✓ {$key}: " . ($key === 'JWT_SECRET' ? 'Set' : $value));
            } else {
                $this->line("   ⚠ {$key}: Not set");
                if ($key === 'JWT_SECRET') $overallHealth = false;
            }
        }

        // 4. System Resources
        $this->line("");
        $this->info("4. System Resources:");
        $this->line("   Memory usage: " . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB");
        $this->line("   Peak memory: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB");

        // 5. Data Integrity
        $this->line("");
        $this->info("5. Data Integrity:");
        try {
            $userCount = DB::table('cas_user.users')->count();
            $clientCount = DB::table('cas_admin.client_systems')->count();
            $auditCount = DB::table('cas_audit.audit_logs')->count();

            $this->line("   Users: {$userCount}");
            $this->line("   Client systems: {$clientCount}");
            $this->line("   Audit logs: {$auditCount}");

            if ($userCount === 0) {
                $this->line("   ⚠ No users found");
            }
        } catch (\Exception $e) {
            $this->line("   ✗ Data check: ERROR - " . $e->getMessage());
            $overallHealth = false;
        }

        // Final result
        $this->line("");
        $this->info("=" . str_repeat("=", 50));

        if ($overallHealth) {
            $this->info("✅ OVERALL HEALTH: GOOD");
            $this->line("All critical systems are operational.");
            return 0;
        } else {
            $this->error("❌ OVERALL HEALTH: ISSUES DETECTED");
            $this->line("Please address the issues above before proceeding.");
            return 1;
        }
    }
}
