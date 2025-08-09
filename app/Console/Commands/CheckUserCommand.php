<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckUserCommand extends Command
{
    protected $signature = 'cas:check-user {email : The email address of the user to check}';
    protected $description = 'Check user account status and authentication details';

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

            $this->info("User Account Status for: {$email}");
            $this->info("=" . str_repeat("=", 50));

            // Basic user info
            $this->line("ID: {$user->id}");
            $this->line("Username: {$user->username}");
            $this->line("Email: {$user->email}");
            $this->line("Full Name: {$user->first_name} {$user->last_name}");
            $this->line("Role: {$user->role}");
            $this->line("Status: " . ($user->is_active ? 'Active' : 'Inactive'));
            $this->line("Created: " . $user->created_at->format('Y-m-d H:i:s'));
            $this->line("Last Login: " . ($user->last_login ? $user->last_login->format('Y-m-d H:i:s') : 'Never'));

            // Check for failed login attempts (if audit logs exist)
            $recentFailedLogins = DB::table('cas_audit.audit_logs')
                ->where('user_id', $user->id)
                ->where('action', 'login_failed')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            $this->line("Failed logins (7 days): {$recentFailedLogins}");

            // Check 2FA status
            if ($user->two_factor_enabled) {
                $this->line("2FA Status: Enabled");
            } else {
                $this->line("2FA Status: Disabled");
            }

            // Check user-client links
            $clientLinks = DB::table('cas_user.user_client_links')
                ->where('user_id', $user->id)
                ->count();

            $this->line("Linked Client Systems: {$clientLinks}");

            $this->info("=" . str_repeat("=", 50));

            if (!$user->is_active) {
                $this->warn("⚠️  Account is INACTIVE");
            }

            if ($recentFailedLogins > 3) {
                $this->warn("⚠️  High number of failed login attempts detected");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Error checking user: " . $e->getMessage());
            return 1;
        }
    }
}
