<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegenerateSecretsCommand extends Command
{
    protected $signature = 'cas:regenerate-secrets {client_id : The client system ID} {--webhook : Also regenerate webhook secret}';
    protected $description = 'Regenerate client system secrets';

    public function handle()
    {
        $clientId = $this->argument('client_id');
        $regenerateWebhook = $this->option('webhook');
        
        try {
            // Find client system
            $client = DB::table('cas_admin.client_systems')
                ->where('id', $clientId)
                ->first();
            
            if (!$client) {
                $this->error("Client system not found: {$clientId}");
                return 1;
            }
            
            $this->info("Regenerating secrets for: {$client->name}");
            
            // Confirm action
            if (!$this->confirm('This will invalidate all existing tokens and require client system reconfiguration. Continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
            
            $updates = [];
            
            // Generate new client secret
            $newClientSecret = 'cs_' . Str::random(32);
            $updates['client_secret'] = $newClientSecret;
            $this->line("✓ Generated new client secret");
            
            // Generate new webhook secret if requested
            if ($regenerateWebhook) {
                $newWebhookSecret = 'wh_' . Str::random(32);
                $updates['webhook_secret'] = $newWebhookSecret;
                $this->line("✓ Generated new webhook secret");
            }
            
            // Update timestamp
            $updates['updated_at'] = now();
            
            // Update database
            DB::table('cas_admin.client_systems')
                ->where('id', $clientId)
                ->update($updates);
            
            // Log the action
            DB::table('cas_audit.audit_logs')
                ->insert([
                    'user_id' => null,
                    'client_system_id' => $clientId,
                    'event_type' => 'admin_action',
                    'action' => 'secrets_regenerated',
                    'description' => 'Client secrets regenerated via artisan command',
                    'success' => true,
                    'details' => json_encode([
                        'regenerated' => array_keys($updates),
                        'regenerated_by' => 'artisan_command',
                        'webhook_included' => $regenerateWebhook
                    ]),
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Laravel Artisan',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            
            $this->info("=" . str_repeat("=", 50));
            $this->info("✓ Secrets regenerated successfully!");
            $this->line("");
            $this->line("New client secret: {$newClientSecret}");
            
            if ($regenerateWebhook && isset($newWebhookSecret)) {
                $this->line("New webhook secret: {$newWebhookSecret}");
            }
            
            $this->line("");
            $this->warn("Important: Update these secrets in your client system configuration immediately!");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error regenerating secrets: " . $e->getMessage());
            return 1;
        }
    }
}