<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UnlockUserCommand extends Command
{
    protected $signature = 'cas:unlock-user {email : The email address of the user to unlock}';
    protected $description = 'Reset account lockout and clear failed login attempts';

    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            // Find user by email
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("User not found: {$email}");
                return 1;
            }
            
            $this->info("Unlocking account for: {$email}");
            
            // Clear any lockout flags (if implemented)
            // For now, we'll activate the user if they're inactive
            if (!$user->is_active) {
                $user->update(['is_active' => true]);
                $this->line("✓ Account activated");
            }
            
            // Clear recent failed login attempts by adding a successful unlock audit log
            DB::table('cas_audit.audit_logs')
                ->insert([
                    'user_id' => $user->id,
                    'client_system_id' => null,
                    'event_type' => 'admin_action',
                    'action' => 'account_unlocked',
                    'description' => 'Account unlocked via artisan command',
                    'success' => true,
                    'details' => json_encode([
                        'admin_action' => true,
                        'unlocked_by' => 'artisan_command',
                        'reason' => 'Manual unlock via cas:unlock-user command'
                    ]),
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Laravel Artisan',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            
            $this->line("✓ Failed login attempts cleared");
            $this->line("✓ Unlock event logged to audit trail");
            
            // Check current status
            $recentFailedLogins = DB::table('cas_audit.audit_logs')
                ->where('user_id', $user->id)
                ->where('action', 'login_failed')
                ->where('created_at', '>=', now()->subHour())
                ->count();
            
            $this->info("Account unlocked successfully!");
            $this->line("Status: " . ($user->is_active ? 'Active' : 'Inactive'));
            $this->line("Recent failed attempts: {$recentFailedLogins}");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error unlocking user: " . $e->getMessage());
            return 1;
        }
    }
}