<?php

namespace App\Livewire\User;

use App\Services\ClientCredentialValidator;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ClientSystem;
use App\Models\UserClientSystem;
use App\Models\AuditLog;
use App\Models\SsoToken;
use App\Services\SsoService;

class UserDashboard extends Component
{
    public $user;
    public $message = '';
    public $messageType = 'success';
    public $loading = true;
    public $processing = false;
    public $refreshData = 0;

    // Modal properties
    public $showLinkModal = false;
    public $selectedSystemId = null;
    public $selectedSystemName = '';
    public $modalUsername = '';
    public $modalPassword = '';

    public function getClientSystemsProperty()
    {
        $this->refreshData;

        $userId = session('user_id');
        if (!$userId) {
            return [];
        }

        $clientSystems = ClientSystem::where('is_active', true)
            ->orderBy('name')
            ->get();

        $result = [];
        foreach ($clientSystems as $system) {
            $userLink = UserClientSystem::where('user_id', $userId)
                ->where('client_system_id', $system->id)
                ->where('is_active', true)
                ->first();

            $showInDashboard = $userLink ? ($userLink->show_in_dashboard ?? true) : true;

            if ($showInDashboard) {
                $result[] = [
                    'id' => $system->id,
                    'name' => $system->name,
                    'description' => $system->description,
                    'callback_url' => $system->callback_url,
                    'is_active' => $system->is_active,
                    'is_linked' => (bool) $userLink,
                    'linked_username' => $userLink ? $userLink->linked_username : null,
                    'last_used' => $userLink ? $userLink->last_used : null,
                    'client_id' => $system->client_id,
                    'created_at' => $system->created_at,
                    'show_in_dashboard' => $showInDashboard,
                ];
            }
        }

        return $result;
    }

    public function mount()
    {
        if (request()->has('error')) {
            $this->showMessage(request()->query('error'), 'error');
        }

        if (request()->has('success')) {
            $this->showMessage(request()->query('success'), 'success');
        }

        $this->loadUserDashboard();
    }

    public function loadUserDashboard()
    {
        $userId = session('user_id');
        if (!$userId) {
            $this->showMessage('Please log in to access dashboard', 'error');
            return;
        }

        try {
            $this->user = User::find($userId);

            if (!$this->user) {
                $this->showMessage('User not found', 'error');
                return;
            }

            $this->loading = false;

        } catch (\Exception $e) {
            $this->showMessage('Failed to load dashboard: ' . $e->getMessage(), 'error');
            $this->loading = false;
        }
    }

    public function openLinkModal($clientSystemId, $systemName = '')
    {
        $system = collect($this->clientSystems)->firstWhere('id', $clientSystemId);
        if ($system) {
            $this->selectedSystemId = $clientSystemId;
            $this->selectedSystemName = $system['name'];
            $this->modalUsername = $system['is_linked'] ? $system['linked_username'] : '';
            $this->modalPassword = '';
            $this->showLinkModal = true;
        }
    }

    public function openEditModal($clientSystemId)
    {
        $system = collect($this->clientSystems)->firstWhere('id', $clientSystemId);
        if ($system) {
            $this->selectedSystemId = $clientSystemId;
            $this->selectedSystemName = $system['name'];
            $this->modalUsername = $system['is_linked'] ? $system['linked_username'] : '';
            $this->modalPassword = '';
            $this->showLinkModal = true;
        }
    }

    public function loginToSystem($clientSystemId)
    {
        if ($this->processing) {
            return;
        }

        Log::info('UserDashboard: loginToSystem called', ['clientSystemId' => $clientSystemId]);

        $this->processing = true;

        $userId = session('user_id');
        if (!$userId) {
            $this->showMessage('Please log in to access client systems', 'error');
            $this->processing = false;
            return;
        }

        try {
            $clientSystem = ClientSystem::find($clientSystemId);
            if (!$clientSystem || !$clientSystem->is_active) {
                $this->showMessage('Client system not found or inactive', 'error');
                $this->processing = false;
                return;
            }

            $userLink = UserClientSystem::where('user_id', $userId)
                ->where('client_system_id', $clientSystemId)
                ->where('is_active', true)
                ->first();

            if (!$userLink) {
                $this->showMessage('Please setup your credentials first', 'error');
                $this->processing = false;
                return;
            }

            $user = User::find($userId);
            if (!$user) {
                $this->showMessage('User not found', 'error');
                $this->processing = false;
                return;
            }

            $ssoService = app(SsoService::class);
            
            $linkedUser = null;
            if ($userLink && $userLink->linked_username) {
                $linkedUser = [
                    'id' => $user->id,
                    'username' => $userLink->linked_username,
                    'email' => $userLink->linked_username,
                ];
                Log::info('UserDashboard: Using linked identity', ['linked_username' => $userLink->linked_username]);
            }
            
            $result = $ssoService->generateWebSsoToken($user, $clientSystem->client_id, request(), $linkedUser);

            if ($result['status'] !== 'success') {
                $this->showMessage('Login failed: ' . $result['message'], 'error');
                $this->processing = false;
                return;
            }

            // Use the secure redirect URL from the service (no sensitive user_id in params)
            $tokenUrl = $result['redirect_url'];
            
            // SsoService handles SsoToken and AuditLog creation

            $userLink = UserClientSystem::find($userLink->id);
            $userLink->update(['last_used' => now()]);

            $this->processing = false;

            $this->dispatch('openInNewTab', $tokenUrl);

            $this->showMessage("Opening {$clientSystem->name} in new tab...", 'success');

        } catch (\Exception $e) {
            $this->showMessage('Login failed: ' . $e->getMessage(), 'error');
            $this->processing = false;
        }
    }

    public function closeLinkModal()
    {
        $this->showLinkModal = false;
        $this->selectedSystemId = null;
        $this->selectedSystemName = '';
        $this->modalUsername = '';
        $this->modalPassword = '';
    }

    public function saveCredentials()
    {
        if ($this->processing) {
            return;
        }

        $this->processing = true;

        $userId = session('user_id');
        if (!$userId) {
            $this->showMessage('Please log in to link client systems', 'error');
            $this->processing = false;
            return;
        }

        if (empty($this->modalUsername) || empty($this->modalPassword)) {
            $this->showMessage('Please provide both username and password', 'error');
            $this->processing = false;
            return;
        }

        try {
            $validator = new ClientCredentialValidator();

            $result = $validator->validateAndStore(
                $this->modalUsername,
                $this->modalPassword,
                $this->selectedSystemId,
                $userId
            );

            if (!$result['success']) {
                $this->showMessage($result['message'], 'error');
                return;
            }

            $this->closeLinkModal();

            $this->refreshData = microtime(true);

            unset($this->clientSystems);

            $this->dispatch('$refresh');
            $this->dispatch('refreshComponent');

            $this->showMessage($result['message'], 'success');

        } catch (\Exception $e) {
            $this->showMessage('Failed to link client system: ' . $e->getMessage(), 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function editCredentials($clientSystemId)
    {
        if ($this->processing) {
            return;
        }

        $this->openLinkModal($clientSystemId);
    }

    public function loginToClientSystem_DISABLED($clientSystemId)
    {
        $userId = session('user_id');

        try {
            $userLink = UserClientSystem::where('user_id', $userId)
                ->where('client_system_id', $clientSystemId)
                ->where('is_active', true)
                ->first();

            if (!$userLink) {
                $this->showMessage('You must link your username first', 'error');
                return;
            }

            $clientSystem = ClientSystem::where('id', $clientSystemId)
                ->where('is_active', true)
                ->first();

            if ($clientSystem) {
                $clientSystem->refresh();
            }

            if (!$clientSystem) {
                $this->showMessage('Client system not found or inactive', 'error');
                return;
            }

            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = now()->addMinutes(5);

            $payload = [
                'user_id' => $userId,
                'username' => $userLink->linked_username,
                'client_system_id' => $clientSystemId,
                'client_id' => $clientSystem->client_id,
                'issued_at' => now()->timestamp,
                'expires_at' => $expiresAt->timestamp,
                'login_type' => 'dashboard_sso'
            ];

            $user = User::find($userId);
            $userRole = $user ? $user->role : 'user';

            SsoToken::create([
                'token' => $token,
                'user_id' => $userId,
                'client_system_id' => $clientSystemId,
                'user_role' => $userRole,
                'token_hash' => $tokenHash,
                'payload' => $payload,
                'expires_at' => $expiresAt,
                'is_active' => true,
                'is_used' => false,
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
            ]);

            $linkModel = UserClientSystem::find($userLink->id);
            $linkModel->update(['last_used' => now()]);

            AuditLog::create([
                'user_id' => $userId,
                'event_type' => 'sso_login',
                'action' => 'dashboard_login',
                'description' => "User logged into client system: {$clientSystem->name}",
                'details' => [
                    'linked_username' => $userLink->linked_username,
                    'token_expires_at' => $expiresAt,
                    'login_method' => 'dashboard_sso',
                    'client_system_id' => $clientSystemId
                ],
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $redirectUrl = $clientSystem->callback_url . '?token=' . $token . '&username=' . urlencode($userLink->linked_username);

            if (empty($clientSystem->callback_url)) {
                $this->showMessage('Client system callback URL not configured', 'error');
                return;
            }

            $this->refreshData = microtime(true);
            $this->dispatch('$refresh');

            $this->showMessage('Login successful! Redirecting to ' . $clientSystem->name, 'success');

            Log::info('Dispatching redirect event', ['url' => $redirectUrl]);
            $this->dispatch('redirect-to-client', ['url' => $redirectUrl]);

        } catch (\Exception $e) {
            $this->showMessage('Login failed: ' . $e->getMessage(), 'error');
            Log::error('Dashboard SSO Login Error: ' . $e->getMessage());
        }
    }

    public function unlinkClientSystem($clientSystemId)
    {
        if ($this->processing) {
            return;
        }

        $this->processing = true;

        $userId = session('user_id');

        try {
            $userLink = UserClientSystem::where('user_id', $userId)
                ->where('client_system_id', $clientSystemId)
                ->first();

            if (!$userLink) {
                $this->showMessage('Link not found', 'error');
                return;
            }

            $clientSystem = ClientSystem::find($clientSystemId);

            AuditLog::create([
                'user_id' => $userId,
                'event_type' => 'user_dashboard',
                'action' => 'unlink_client_system',
                'description' => "User unlinked client system: {$clientSystem->name}",
                'details' => [
                    'unlinked_username' => $userLink->linked_username,
                    'client_system_id' => $clientSystemId
                ],
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $userLink->delete();

            $this->refreshData = microtime(true);
            unset($this->clientSystems);
            $this->dispatch('$refresh');

            $this->showMessage('Successfully unlinked from client system', 'success');

        } catch (\Exception $e) {
            $this->showMessage('Failed to unlink: ' . $e->getMessage(), 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function openAddModal()
    {
        $this->showMessage('Contact your administrator to add new client systems', 'info');
    }

    public function showMessage($message, $type = 'success')
    {
        $this->message = $message;
        $this->messageType = $type;
        $this->dispatch('show-message', message: $message, type: $type);
    }

    public function clearMessage()
    {
        $this->message = '';
    }

    public function render()
    {
        return view('user.livewire.user-dashboard');
    }
}
