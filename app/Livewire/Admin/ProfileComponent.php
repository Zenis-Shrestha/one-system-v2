<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class ProfileComponent extends Component
{
    public $username;
    public $email;
    public $first_name;
    public $last_name;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    public $showPasswordSection = false;

    // 2FA properties
    public $two_factor_enabled = false;
    public $two_factor_secret;
    public $show2FASection = false;
    public $qr_code_url;
    public $backup_codes = [];

    protected function rules()
    {
        return [
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'current_password' => 'required_with:new_password|current_password',
            'new_password' => ['nullable', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ];
    }

    public function mount()
    {
        $user = auth()->user();
        $userId = auth()->id() ?? session('user_id');

        if (!$user && $userId) {
            $user = User::find($userId);
        }

        if (!$user) {
            $adminUser = User::where('role', 'admin')->first();
            if ($adminUser) {
                $user = $adminUser;
                session(['user_id' => $user->id, 'username' => $user->username, 'role' => $user->role]);
            }
        }

        if ($user) {
            $this->username = $user->username ?? '';
            $this->email = $user->email ?? '';
            $this->first_name = $user->first_name ?? '';
            $this->last_name = $user->last_name ?? '';
            $this->two_factor_enabled = (bool) $user->two_factor_enabled;
            $this->two_factor_secret = $user->two_factor_secret;
            $this->backup_codes = $user->two_factor_backup_codes ?? [];
        } else {
            return redirect()->route('login');
        }
    }

    public function updateProfile()
    {
        $userId = auth()->id() ?? session('user_id');

        if (!$userId) {
            $adminUser = User::where('role', 'admin')->first();
            if ($adminUser) {
                $userId = $adminUser->id;
            }
        }

        $this->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        $existingUser = User::where('username', $this->username)
            ->where('id', '!=', $userId)
            ->first();
        if ($existingUser) {
            $this->addError('username', 'The username has already been taken.');
            return;
        }

        $existingEmail = User::where('email', $this->email)
            ->where('id', '!=', $userId)
            ->first();
        if ($existingEmail) {
            $this->addError('email', 'The email has already been taken.');
            return;
        }

        try {
            $user = User::find($userId);
            $user->update([
                'username' => $this->username,
                'email' => $this->email,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
            ]);

            session()->flash('message', 'Profile updated successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    public function changePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $user = auth()->user();
        $userId = auth()->id() ?? session('user_id');

        if (!$user && $userId) {
            $user = User::find($userId);
        }

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The current password is incorrect.');
            return;
        }

        try {
            $user->update([
                'password' => Hash::make($this->new_password),
            ]);

            $this->current_password = '';
            $this->new_password = '';
            $this->new_password_confirmation = '';
            $this->showPasswordSection = false;

            session()->flash('message', 'Password changed successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to change password: ' . $e->getMessage());
        }
    }

    public function togglePasswordSection()
    {
        $this->showPasswordSection = !$this->showPasswordSection;

        if (!$this->showPasswordSection) {
            $this->current_password = '';
            $this->new_password = '';
            $this->new_password_confirmation = '';
            $this->resetErrorBag(['current_password', 'new_password']);
        }
    }

    public function toggle2FASection()
    {
        $this->show2FASection = !$this->show2FASection;
    }

    public function enable2FA()
    {
        $user = auth()->user();
        $userId = auth()->id() ?? session('user_id');

        if (!$user && $userId) {
            $user = User::find($userId);
        }

        if (!$user) {
            $adminUser = User::where('role', 'admin')->first();
            if ($adminUser) {
                $user = $adminUser;
                $userId = $user->id;
            }
        }

        try {
            $secret = $this->generateSecretKey();

            $backupCodes = $this->generateBackupCodes();

            $user->update([
                'two_factor_enabled' => true,
                'two_factor_secret' => $secret,
                'two_factor_backup_codes' => $backupCodes,
            ]);

            $this->two_factor_enabled = true;
            $this->two_factor_secret = $secret;
            $this->backup_codes = $backupCodes;
            $this->qr_code_url = $this->generateQRCodeUrl($user->email, $secret);

            DB::table('cas_audit.audit_logs')->insert([
                'user_id' => $user->id,
                'client_system_id' => null,
                'event_type' => 'security_action',
                'action' => '2fa_enabled',
                'description' => 'Two-factor authentication enabled',
                'success' => true,
                'details' => json_encode([
                    'method' => 'profile_settings',
                    'backup_codes_generated' => count($backupCodes)
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            session()->flash('message', '2FA enabled successfully! Please save your backup codes.');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to enable 2FA: ' . $e->getMessage());
        }
    }

    public function disable2FA()
    {
        $user = auth()->user();
        $userId = auth()->id() ?? session('user_id');

        if (!$user && $userId) {
            $user = User::find($userId);
        }

        if (!$user) {
            $adminUser = User::where('role', 'admin')->first();
            if ($adminUser) {
                $user = $adminUser;
                $userId = $user->id;
            }
        }

        try {
            $user->update([
                'two_factor_enabled' => false,
                'two_factor_secret' => null,
                'two_factor_backup_codes' => null,
            ]);

            $this->two_factor_enabled = false;
            $this->two_factor_secret = null;
            $this->backup_codes = [];
            $this->qr_code_url = null;

            DB::table('cas_audit.audit_logs')->insert([
                'user_id' => $user->id,
                'client_system_id' => null,
                'event_type' => 'security_action',
                'action' => '2fa_disabled',
                'description' => 'Two-factor authentication disabled',
                'success' => true,
                'details' => json_encode([
                    'method' => 'profile_settings'
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            session()->flash('message', '2FA disabled successfully.');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to disable 2FA: ' . $e->getMessage());
        }
    }

    public function regenerateBackupCodes()
    {
        $user = auth()->user();
        $userId = auth()->id() ?? session('user_id');

        if (!$user && $userId) {
            $user = User::find($userId);
        }

        if (!$user) {
            $adminUser = User::where('role', 'admin')->first();
            if ($adminUser) {
                $user = $adminUser;
                $userId = $user->id;
            }
        }

        try {
            $backupCodes = $this->generateBackupCodes();

            $user->update([
                'two_factor_backup_codes' => $backupCodes,
            ]);

            $this->backup_codes = $backupCodes;

            DB::table('cas_audit.audit_logs')->insert([
                'user_id' => $user->id,
                'client_system_id' => null,
                'event_type' => 'security_action',
                'action' => '2fa_backup_codes_regenerated',
                'description' => 'Two-factor authentication backup codes regenerated',
                'success' => true,
                'details' => json_encode([
                    'method' => 'profile_settings',
                    'codes_count' => count($backupCodes)
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            session()->flash('message', 'Backup codes regenerated successfully.');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to regenerate backup codes: ' . $e->getMessage());
        }
    }

    private function generateSecretKey()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 32; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    private function generateBackupCodes()
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    private function generateQRCodeUrl($email, $secret)
    {
        $appName = config('app.name', 'CAS System');
        $issuer = urlencode($appName);
        $email = urlencode($email);

        $otpUrl = "otpauth://totp/{$email}?secret={$secret}&issuer={$issuer}";

        return "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=" . urlencode($otpUrl);
    }

    public function render()
    {
        return view('livewire.admin.profile-component');
    }
}
