<?php

namespace App\Livewire\User;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\UserClientSystem;
use App\Models\ClientSystem;
use PragmaRX\Google2FA\Google2FA;
use App\Services\QrCodeService;

class UserProfile extends Component
{
    public $user;
    public $currentPassword = '';
    public $newPassword = '';
    public $newPasswordConfirmation = '';
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $message = '';
    public $messageType = 'success';
    public $processing = false;
    public $showPasswordForm = false;
    public $show2FAForm = false;
    public $twoFactorQrCode = '';
    public $twoFactorSecret = '';
    public $twoFactorCode = '';
    public $linkedSystems = [];
    public $activeTab = 'profile';

    public function mount()
    {
        $this->loadUserProfile();
    }

    public function loadUserProfile()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $this->user = User::find($userId);
        if (!$this->user) {
            $this->showMessage('User not found', 'error');
            return;
        }

        $this->first_name = $this->user->first_name ?? '';
        $this->last_name = $this->user->last_name ?? '';
        $this->email = $this->user->email;

        $this->loadLinkedSystems();
    }

    public function updateProfile()
    {
        if ($this->processing) {
            return;
        }

        $this->processing = true;

        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $existingUser = User::where('email', $this->email)
            ->where('id', '!=', $this->user->id)
            ->first();

        if ($existingUser) {
            $this->addError('email', 'The email has already been taken.');
            $this->processing = false;
            return;
        }

        try {
            $this->user->update([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
            ]);

            AuditLog::create([
                'user_id' => $this->user->id,
                'event_type' => 'profile_update',
                'action' => 'update_profile',
                'description' => 'User updated profile information',
                'details' => [
                    'updated_fields' => ['first_name', 'last_name', 'email']
                ],
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $this->dispatch('$refresh');
            $this->showMessage('Profile updated successfully', 'success');

        } catch (\Exception $e) {
            $this->showMessage('Failed to update profile: ' . $e->getMessage(), 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function changePassword()
    {
        if ($this->processing) {
            return;
        }

        $this->processing = true;

        $this->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|confirmed',
        ]);

        if ($this->getErrorBag()->count() > 0) {
            $this->processing = false;
            return;
        }

        try {
            if (!Hash::check($this->currentPassword, $this->user->password)) {
                $this->showMessage('Current password is incorrect', 'error');
                $this->processing = false;
                return;
            }

            $this->user->update([
                'password' => Hash::make($this->newPassword),
                'password_changed_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $this->user->id,
                'event_type' => 'security',
                'action' => 'password_change',
                'description' => 'User changed password',
                'details' => ['changed_at' => now()],
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $this->currentPassword = '';
            $this->newPassword = '';
            $this->newPasswordConfirmation = '';
            $this->showPasswordForm = false;

            $this->dispatch('$refresh');
            $this->showMessage('Password changed successfully', 'success');

        } catch (\Exception $e) {
            $this->showMessage('Failed to change password: ' . $e->getMessage(), 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function setup2FA()
    {
        try {
            $google2fa = new Google2FA();
            $this->twoFactorSecret = $google2fa->generateSecretKey();

            $qrCodeUrl = $google2fa->getQRCodeUrl(
                'CAS Authentication System',
                $this->user->email,
                $this->twoFactorSecret
            );

            $qrCodeService = new QrCodeService();
            $this->twoFactorQrCode = $qrCodeService->generate2FAQrCode($qrCodeUrl, $this->user->id);
            $this->show2FAForm = true;

        } catch (\Exception $e) {
            Log::error('2FA setup failed', ['user_id' => $this->user?->id, 'exception' => $e]);
            $this->showMessage('Failed to set up two-factor authentication. Please try again.', 'error');
        }
    }



    public function enable2FA()
    {
        $this->validate([
            'twoFactorCode' => 'required|digits:6',
        ]);

        try {
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($this->twoFactorSecret, $this->twoFactorCode);

            if (!$valid) {
                $this->showMessage('Invalid verification code. Please try again.', 'error');
                return;
            }

            $backupCodes = $this->generateBackupCodes();

            $this->user->update([
                'two_factor_secret' => $this->twoFactorSecret,
                'two_factor_enabled' => true,
                'two_factor_backup_codes' => $backupCodes,
            ]);

            DB::table('cas_audit.audit_logs')->insert([
                'user_id' => $this->user->id,
                'client_system_id' => null,
                'event_type' => 'security_action',
                'action' => '2fa_enabled',
                'description' => 'User enabled two-factor authentication',
                'details' => json_encode([
                    'enabled_at' => now(),
                    'backup_codes_generated' => count($backupCodes)
                ]),
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->show2FAForm = false;
            $this->twoFactorCode = '';
            $this->twoFactorSecret = '';

            $this->showMessage('Two-factor authentication enabled successfully! Please save your backup codes securely.', 'success');
            $this->loadUserProfile();
        } catch (\Exception $e) {
            Log::error('2FA enable failed', ['user_id' => $this->user?->id, 'exception' => $e]);
            $this->showMessage('Failed to enable two-factor authentication. Please try again.', 'error');
        }
    }

    public function disable2FA()
    {
        try {
            $this->user->update([
                'two_factor_secret' => null,
                'two_factor_enabled' => false,
                'two_factor_backup_codes' => null,
            ]);

            DB::table('cas_audit.audit_logs')->insert([
                'user_id' => $this->user->id,
                'client_system_id' => null,
                'event_type' => 'security_action',
                'action' => '2fa_disabled',
                'description' => 'User disabled two-factor authentication',
                'details' => json_encode(['disabled_at' => now()]),
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->showMessage('Two-factor authentication disabled', 'success');
            $this->loadUserProfile();

        } catch (\Exception $e) {
            Log::error('2FA disable failed', ['user_id' => $this->user?->id, 'exception' => $e]);
            $this->showMessage('Failed to disable two-factor authentication. Please try again.', 'error');
        }
    }

    private function generateBackupCodes()
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    public function showMessage($message, $type = 'success')
    {
        $this->message = $message;
        $this->messageType = $type;
        $this->dispatch('show-message');
    }

    public function clearMessage()
    {
        $this->message = '';
    }

    public function loadLinkedSystems()
    {
        if (!$this->user) {
            return;
        }

        $allClientSystems = ClientSystem::all();

        $userDashboardSettings = UserClientSystem::where('user_id', $this->user->id)
            ->with('clientSystem')
            ->get()
            ->keyBy('client_system_id');

        logger("Found " . $allClientSystems->count() . " total client systems, " . $userDashboardSettings->count() . " with user settings for user " . $this->user->id);

        $this->linkedSystems = $allClientSystems->map(function ($clientSystem) use ($userDashboardSettings) {
            $userSetting = $userDashboardSettings->get($clientSystem->id);
            $showInDashboard = $userSetting ? $userSetting->show_in_dashboard : false;

            return [
                'id' => $userSetting ? $userSetting->id : null,
                'client_system_id' => $clientSystem->id,
                'name' => $clientSystem->name,
                'description' => $clientSystem->description ?? '',
                'callback_url' => $clientSystem->callback_url ?? '',
                'is_active' => $clientSystem->is_active,
                'show_in_dashboard' => (bool) $showInDashboard,
                'created_at' => $userSetting ? $userSetting->created_at : null,
                'last_used' => $userSetting ? $userSetting->last_used : null,
                'linked_username' => $userSetting ? $userSetting->linked_username : null,
            ];
        })->toArray();

        logger("All systems data: " . json_encode($this->linkedSystems));
    }

    public function toggleSystemVisibility($userClientSystemId, $clientSystemId = null)
    {
        if ($this->processing) {
            return;
        }

        $this->processing = true;

        logger("toggleSystemVisibility called for user {$this->user->id}: userClientSystemId={$userClientSystemId}, clientSystemId={$clientSystemId}");

        try {
            if ($userClientSystemId === null || $userClientSystemId === 'null') {
                if (!$clientSystemId) {
                    $this->showMessage('Client system ID is required', 'error');
                    return;
                }

                $clientSystem = ClientSystem::find($clientSystemId);
                if (!$clientSystem) {
                    $this->showMessage('Client system not found', 'error');
                    return;
                }

                $userClientSystem = UserClientSystem::create([
                    'user_id' => $this->user->id,
                    'client_system_id' => $clientSystemId,
                    'show_in_dashboard' => true,
                    'linked_username' => null,
                    'encrypted_password' => null,
                ]);

                $newVisibility = true;
                $currentVisibility = false;
                $systemName = $clientSystem->name;
            } else {
                $userClientSystem = UserClientSystem::where('user_id', $this->user->id)
                    ->where('id', $userClientSystemId)
                    ->with('clientSystem')
                    ->first();

                if (!$userClientSystem) {
                    $this->showMessage('System not found', 'error');
                    return;
                }

                $currentVisibility = (bool) $userClientSystem->show_in_dashboard;
                $newVisibility = !$currentVisibility;

                $userClientSystem->update(['show_in_dashboard' => $newVisibility]);
                $systemName = $userClientSystem->clientSystem->name ?? 'Unknown System';
            }

            AuditLog::create([
                'user_id' => $this->user->id,
                'client_system_id' => $userClientSystem->client_system_id,
                'event_type' => 'client_system_management',
                'action' => $newVisibility ? 'show_in_dashboard' : 'hide_from_dashboard',
                'description' => 'User ' . ($newVisibility ? 'enabled' : 'disabled') . ' client system visibility in dashboard',
                'details' => [
                    'system_name' => $systemName,
                    'show_in_dashboard' => $newVisibility,
                    'previous_visibility' => $currentVisibility
                ],
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $this->showMessage(
                $newVisibility
                    ? 'System will now appear in your dashboard'
                    : 'System hidden from dashboard',
                'success'
            );

            $this->loadLinkedSystems();
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            logger("Error in toggleSystemVisibility: " . $e->getMessage());
            logger("Stack trace: " . $e->getTraceAsString());
            $this->showMessage('Error updating system visibility: ' . $e->getMessage(), 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function removeLinkedSystem($systemId)
    {
        if ($this->processing) {
            return;
        }

        $this->processing = true;

        try {
            $userClientSystem = UserClientSystem::where('user_id', $this->user->id)
                ->where('id', $systemId)
                ->with('clientSystem')
                ->first();

            if (!$userClientSystem) {
                $this->showMessage('System not found', 'error');
                return;
            }

            $systemName = $userClientSystem->clientSystem->name ?? 'Unknown System';

            AuditLog::create([
                'user_id' => $this->user->id,
                'client_system_id' => $userClientSystem->client_system_id,
                'event_type' => 'client_system_management',
                'action' => 'unlink_system',
                'description' => 'User removed linked client system',
                'details' => [
                    'system_name' => $systemName,
                    'unlinked_at' => now()
                ],
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $userClientSystem->delete();

            $this->loadLinkedSystems();
            $this->dispatch('$refresh');
            $this->showMessage("Successfully removed {$systemName} from your account", 'success');
        } catch (\Exception $e) {
            $this->showMessage('Error removing system: ' . $e->getMessage(), 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('user.livewire.user-profile');
    }
}
