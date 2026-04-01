<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use App\Models\SecuritySetting;
use App\Models\UserSecurity;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class SecuritySettingsComponent extends Component
{
    public $processing = false;
    public $enable_2fa = false;
    public $enable_forgot_password = true;
    public $password_reset_expiry = 60;
    public $max_reset_attempts = 3;
    public $lockout_duration = 30;
    public $require_email_verification = true;

    // 2FA Settings
    public $google2fa_secret = '';
    public $verification_code = '';
    public $qr_code_url = '';
    public $is_2fa_enabled = false;
    public $backup_codes = [];

    // Email Settings
    public $smtp_host = '';
    public $smtp_port = 587;
    public $smtp_username = '';
    public $smtp_password = '';
    public $smtp_encryption = 'tls';
    public $from_email = '';
    public $from_name = 'CAS System';

    protected $rules = [
        'password_reset_expiry' => 'required|integer|min:15|max:1440',
        'max_reset_attempts' => 'required|integer|min:1|max:10',
        'lockout_duration' => 'required|integer|min:5|max:1440',
        'smtp_host' => 'required_if:enable_forgot_password,true|nullable|string',
        'smtp_port' => 'required_if:enable_forgot_password,true|nullable|integer|min:1|max:65535',
        'smtp_username' => 'required_if:enable_forgot_password,true|nullable|string',
        'smtp_password' => 'required_if:enable_forgot_password,true|nullable|string',
        'from_email' => 'required_if:enable_forgot_password,true|nullable|email',
        'from_name' => 'required_if:enable_forgot_password,true|nullable|string|max:255',
        'verification_code' => 'required_with:google2fa_secret|digits:6'
    ];

    public function mount()
    {
        $this->loadSettings();
        $this->loadUserSecurity();
    }

    public function loadSettings()
    {
        SecuritySetting::initializeDefaults();

        $settings = SecuritySetting::current();

        $this->enable_forgot_password = $settings->enable_forgot_password ?? true;
        $this->password_reset_expiry = $settings->password_reset_expiry ?? 60;
        $this->max_reset_attempts = $settings->max_reset_attempts ?? 3;
        $this->lockout_duration = $settings->lockout_duration ?? 30;

        $this->require_email_verification = $settings->require_email_verification ?? true;
        $this->smtp_host = $settings->smtp_host ?? '';
        $this->smtp_port = $settings->smtp_port ?? 587;
        $this->smtp_username = $settings->smtp_username ?? '';
        $this->smtp_password = $settings->smtp_password ?? '';
        $this->smtp_encryption = $settings->smtp_encryption ?? 'tls';
        $this->from_email = $settings->from_email ?? '';
        $this->from_name = $settings->from_name ?? 'CAS System';
    }

    public function loadUserSecurity()
    {
        $userId = auth()->id() ?? session('user_id');
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $this->is_2fa_enabled = $user->two_factor_enabled ?? false;
                $userSecurity = UserSecurity::forUser($userId);
                $this->backup_codes = $userSecurity->two_factor_backup_codes ?? [];
            }
        }
    }

    public function saveSettings()
    {
        if ($this->processing) {
            return;
        }

        $this->processing = true;

        $this->validate();

        try {
            $updateData = [
                'enable_forgot_password' => $this->enable_forgot_password,
                'password_reset_expiry' => $this->password_reset_expiry,
                'max_reset_attempts' => $this->max_reset_attempts,
                'lockout_duration' => $this->lockout_duration,
                'require_email_verification' => $this->require_email_verification,
                'smtp_host' => $this->smtp_host,
                'smtp_port' => $this->smtp_port,
                'smtp_username' => $this->smtp_username,
                'smtp_password' => $this->smtp_password,
                'smtp_encryption' => $this->smtp_encryption,
                'from_email' => $this->from_email,
                'from_name' => $this->from_name,
            ];

            $result = SecuritySetting::updateSettings($updateData);

            if ($result) {
                session()->flash('message', 'Security settings updated successfully!');
                $this->dispatch('settings-saved');
                $this->dispatch('$refresh');
            } else {
                session()->flash('error', 'Failed to update settings. Please try again.');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save settings: ' . $e->getMessage());
            \Log::error('SecuritySettings Save Error: ' . $e->getMessage());
        } finally {
            $this->processing = false;
        }
    }

    public function generate2FA()
    {
        $userId = auth()->id() ?? session('user_id');
        if (!$userId) {
            session()->flash('error', 'You must be logged in to generate 2FA codes.');
            return;
        }

        $google2fa = new Google2FA();
        $this->google2fa_secret = $google2fa->generateSecretKey();

        $user = User::find($userId);
        $companyName = 'CAS System';
        $companyEmail = $user ? $user->email : 'admin@innovativesolution.com.np';

        $this->qr_code_url = $google2fa->getQRCodeUrl(
            $companyName,
            $companyEmail,
            $this->google2fa_secret
        );
    }

    public function enable2FA()
    {
        $userId = auth()->id() ?? session('user_id');
        if (!$userId) {
            session()->flash('error', 'You must be logged in to enable 2FA.');
            return;
        }

        $this->validate(['verification_code' => 'required|digits:6']);

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($this->google2fa_secret, $this->verification_code);

        if (!$valid) {
            $this->addError('verification_code', 'Invalid verification code. Please try again.');
            return;
        }

        try {
            $user = User::find($userId);
            $user->update([
                'two_factor_enabled' => true,
                'two_factor_secret' => $this->google2fa_secret
            ]);

            $userSecurity = UserSecurity::forUser($userId);
            $backupCodes = $userSecurity->generateBackupCodes();
            $userSecurity->update([
                'two_factor_enabled' => true,
                'two_factor_secret' => $this->google2fa_secret,
                'two_factor_backup_codes' => $backupCodes
            ]);

            $this->is_2fa_enabled = true;
            $this->backup_codes = $backupCodes;
            $this->verification_code = '';
            $this->google2fa_secret = '';
            $this->qr_code_url = '';

            session()->flash('message', '2FA enabled successfully! Please save your backup codes.');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to enable 2FA: ' . $e->getMessage());
        }
    }

    public function disable2FA()
    {
        $userId = auth()->id() ?? session('user_id');
        if (!$userId) {
            session()->flash('error', 'You must be logged in to disable 2FA.');
            return;
        }

        try {
            $user = User::find($userId);
            $user->update([
                'two_factor_enabled' => false,
                'two_factor_secret' => null
            ]);

            $userSecurity = UserSecurity::forUser($userId);
            $userSecurity->update([
                'two_factor_enabled' => false,
                'two_factor_secret' => null,
                'two_factor_backup_codes' => []
            ]);

            $this->is_2fa_enabled = false;
            $this->backup_codes = [];

            session()->flash('message', '2FA disabled successfully.');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to disable 2FA: ' . $e->getMessage());
        }
    }

    public function regenerateBackupCodes()
    {
        $userId = session('user_id');
        if (!$userId) {
            session()->flash('error', 'You must be logged in to regenerate backup codes.');
            return;
        }

        try {
            $userSecurity = UserSecurity::forUser($userId);
            $backupCodes = $userSecurity->generateBackupCodes();

            $this->backup_codes = $backupCodes;

            session()->flash('message', 'Backup codes regenerated successfully.');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to regenerate backup codes: ' . $e->getMessage());
        }
    }

    public function testEmailConfiguration()
    {
        try {
            if (empty($this->smtp_host) || empty($this->smtp_username) || empty($this->smtp_password) || empty($this->from_email)) {
                session()->flash('error', 'Please fill in all required email configuration fields.');
                return;
            }

            $config = [
                'transport' => 'smtp',
                'host' => $this->smtp_host,
                'port' => $this->smtp_port,
                'encryption' => $this->smtp_encryption,
                'username' => $this->smtp_username,
                'password' => $this->smtp_password,
                'timeout' => 10,
            ];

            $transport = new EsmtpTransport(
                $this->smtp_host,
                $this->smtp_port,
                $this->smtp_encryption === 'tls'
            );

            $transport->setUsername($this->smtp_username);
            $transport->setPassword($this->smtp_password);

            $transport->start();
            $transport->stop();

            session()->flash('message', 'Email configuration is valid and working!');

        } catch (\Exception $e) {
            session()->flash('error', 'Email test failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.security-settings-component');
    }
}
