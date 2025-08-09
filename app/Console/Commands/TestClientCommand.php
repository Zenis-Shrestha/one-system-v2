<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TestClientCommand extends Command
{
    protected $signature = 'cas:test-client {client_id : The client system ID to test}';
    protected $description = 'Check client system credentials and connectivity';

    public function handle()
    {
        $clientId = $this->argument('client_id');
        
        try {
            // Find client system
            $client = DB::table('cas_admin.client_systems')
                ->where('id', $clientId)
                ->first();
            
            if (!$client) {
                $this->error("Client system not found: {$clientId}");
                return 1;
            }
            
            $this->info("Testing Client System: {$client->name}");
            $this->info("=" . str_repeat("=", 50));
            
            // Display client info
            $this->line("ID: {$client->id}");
            $this->line("Name: {$client->name}");
            $this->line("Description: {$client->description}");
            $this->line("Domain: {$client->domain}");
            $this->line("Status: " . ($client->is_active ? 'Active' : 'Inactive'));
            $this->line("Created: {$client->created_at}");
            
            // Test credentials
            if ($client->client_id && $client->client_secret) {
                $this->line("");
                $this->info("Credentials Check:");
                $this->line("✓ Client ID: {$client->client_id}");
                $this->line("✓ Client Secret: " . str_repeat('*', strlen($client->client_secret) - 4) . substr($client->client_secret, -4));
            } else {
                $this->warn("⚠️  Missing credentials");
            }
            
            // Test domain connectivity if available
            if ($client->domain) {
                $this->line("");
                $this->info("Connectivity Test:");
                
                try {
                    $response = Http::timeout(10)->get("https://{$client->domain}");
                    if ($response->successful()) {
                        $this->line("✓ Domain reachable: {$client->domain}");
                    } else {
                        $this->line("⚠️  Domain returned HTTP {$response->status()}: {$client->domain}");
                    }
                } catch (\Exception $e) {
                    $this->line("⚠️  Domain unreachable: {$client->domain} - " . $e->getMessage());
                }
            }
            
            // Check webhook URL if exists
            if (isset($client->webhook_url) && $client->webhook_url) {
                try {
                    $response = Http::timeout(5)->head($client->webhook_url);
                    $this->line("✓ Webhook URL responsive: {$client->webhook_url}");
                } catch (\Exception $e) {
                    $this->line("⚠️  Webhook URL issue: " . $e->getMessage());
                }
            }
            
            // Check recent activity
            $recentActivity = DB::table('cas_audit.audit_logs')
                ->where('client_system_id', $client->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
            
            $this->line("");
            $this->line("Recent activity (7 days): {$recentActivity} events");
            
            if (!$client->is_active) {
                $this->warn("⚠️  Client system is INACTIVE");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error testing client: " . $e->getMessage());
            return 1;
        }
    }
}