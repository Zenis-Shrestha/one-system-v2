<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClientSystem;

class ClientSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientSystems = [
            [
                'name' => 'Laravel Customer Portal',
                'description' => 'Laravel-based customer portal with e-commerce features',
                'callback_url' => 'http://127.0.0.1:9001/cas/callback',
                'client_id' => 'laravel_customer_portal_id',
                'client_secret' => 'laravel_portal_secret',
                'allowed_scopes' => json_encode(['read', 'write', 'orders']),
                'is_active' => true,
                'credentials_viewed' => true,
                'server_config' => json_encode([
                    'domain' => 'http://127.0.0.1:9001',
                    'webhook_secret' => 'laravel_portal_webhook_secret',
                    'signature_validation_enabled' => true
                ]),
            ]
        ];

        foreach ($clientSystems as $systemData) {
            ClientSystem::updateOrCreate(
                ['name' => $systemData['name']],
                $systemData
            );
        }

        $this->command->info('✅ Created ' . count($clientSystems) . ' client systems with clean migration structure');
    }
}
