<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientSystemsManager extends Component
{
    // Component state
    public $clientSystems = [];
    public $loading = true;
    public $processing = false;

    // Form data
    public $showCreateForm = false;
    public $name = '';
    public $description = '';
    public $callback_url = '';


    // Credential display
    public $showCredentials = false;
    public $newCredentials = [
        'client_id' => '',
        'client_secret' => '',
        'webhook_secret' => ''
    ];
    public $createdSystemName = '';

    // Messages
    public $message = '';
    public $messageType = 'success';

    // Editing
    public $editingSystemId = null;
    public $editName = '';
    public $editDescription = '';
    public $editCallbackUrl = '';
    public $editStatus = 'active';

    // Regeneration
    public $showRegenerateModal = false;
    public $regenerateSystemId = null;
    public $regenerateReason = '';
    public $regenerateSystemName = '';

    public function mount()
    {
        $this->loadClientSystems();
    }

    public function loadClientSystems()
    {
        try {
            $this->loading = true;

            $systems = DB::table('cas_admin.client_systems')
                ->select(
                    'id',
                    'name',
                    'description',
                    'callback_url',
                    'client_id',
                    'is_active',
                    'credentials_viewed',
                    'credentials_viewed_at',
                    'allowed_scopes',
                    'server_config',
                    'created_at',
                    'updated_at'
                )
                ->orderBy('created_at', 'desc')
                ->get();

            $this->clientSystems = $systems->map(function ($system) {
                return [
                    'id' => $system->id,
                    'name' => $system->name,
                    'description' => $system->description,
                    'callback_url' => $system->callback_url,
                    'client_id' => $system->client_id,
                    'is_active' => $system->is_active,
                    'status' => $system->is_active ? 'active' : 'inactive',
                    'created_at' => $system->created_at,
                    'last_accessed' => null,
                    'allowed_scopes' => $system->allowed_scopes ? json_decode($system->allowed_scopes, true) : [],
                    'server_config' => $system->server_config ? json_decode($system->server_config, true) : [],
                    'security_status' => [
                        'credentials_viewed' => (bool) $system->credentials_viewed,
                        'credentials_viewed_at' => $system->credentials_viewed_at,
                        'credentials_regenerated_at' => $system->updated_at,
                        'can_view_credentials' => !$system->credentials_viewed,
                    ]
                ];
            })->toArray();

        } catch (\Exception $e) {
            $this->showMessage('Failed to load client systems: ' . $e->getMessage(), 'error');
        } finally {
            $this->loading = false;
        }
    }

    public function createClientSystem()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'callback_url' => 'required|url',
        ]);

        try {
            $clientSecret = bin2hex(random_bytes(32));
            $webhookSecret = bin2hex(random_bytes(32));
            $clientId = 'client_' . bin2hex(random_bytes(8));

            $clientSystemId = DB::table('cas_admin.client_systems')->insertGetId([
                'name' => $this->name,
                'description' => $this->description,
                'callback_url' => $this->callback_url,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'webhook_secret' => $webhookSecret,
                'allowed_scopes' => json_encode(['read', 'write']),
                'is_active' => true,
                'credentials_viewed' => false,
                'credentials_shown' => false,
                'server_config' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('cas_audit.audit_logs')->insert([
                'user_id' => session('user_id'),
                'client_system_id' => $clientSystemId,
                'event_type' => 'client_system_created',
                'action' => 'create_client_system',
                'description' => "Created client system: {$this->name}",
                'details' => json_encode([
                    'client_system_name' => $this->name,
                    'callback_url' => $this->callback_url,
                    'client_id' => $clientId,
                    'credentials_generated' => true
                ]),
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->newCredentials = [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'webhook_secret' => $webhookSecret
            ];

            $this->createdSystemName = $this->name;

            $this->resetCreateForm();
            $this->showCreateForm = false;
            $this->showCredentials = true;

            $this->loadClientSystems();

            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            $this->showMessage('Failed to create client system: ' . $e->getMessage(), 'error');
        }
    }

    public function closeCredentials()
    {
        $clientSystem = collect($this->clientSystems)->firstWhere('client_id', $this->newCredentials['client_id']);

        if ($clientSystem) {
            try {
                DB::table('cas_admin.client_systems')
                    ->where('id', $clientSystem['id'])
                    ->update([
                        'credentials_shown' => true,
                        'credentials_viewed_at' => now(),
                        'credentials_viewed_by' => (int)session('user_id'),
                        'updated_at' => now()
                    ]);

                DB::table('cas_audit.audit_logs')->insert([
                    'user_id' => session('user_id'),
                    'client_system_id' => $clientSystem['id'],
                    'event_type' => 'security_event',
                    'action' => 'credentials_viewed',
                    'description' => 'Client system credentials marked as viewed',
                    'details' => json_encode([
                        'viewed_at' => now(),
                        'security_note' => 'Credentials will no longer be accessible'
                    ]),
                    'success' => true,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to mark credentials as viewed', ['error' => $e->getMessage()]);
            }
        }

        $this->showCredentials = false;
        $this->newCredentials = ['client_id' => '', 'client_secret' => '', 'webhook_secret' => ''];
        $this->createdSystemName = '';
        $this->loadClientSystems();

        $this->dispatch('$refresh');
    }

    public function startEdit($systemId)
    {
        if ($this->processing) {
            return;
        }

        $system = collect($this->clientSystems)->firstWhere('id', $systemId);

        if ($system) {
            $this->editingSystemId = $systemId;
            $this->editName = $system['name'];
            $this->editDescription = $system['description'] ?? '';
            $this->editCallbackUrl = $system['callback_url'];
            $this->editStatus = $system['status'];
        }
    }

    public function updateClientSystem()
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editDescription' => 'nullable|string|max:500',
            'editCallbackUrl' => 'required|url',
            'editStatus' => 'required|in:active,inactive',
        ]);

        try {
            $updateData = [
                'name' => $this->editName,
                'description' => $this->editDescription,
                'callback_url' => $this->editCallbackUrl,
                'is_active' => $this->editStatus === 'active',
                'updated_at' => now()
            ];

            DB::table('cas_admin.client_systems')
                ->where('id', $this->editingSystemId)
                ->update($updateData);

            DB::table('cas_audit.audit_logs')->insert([
                'user_id' => session('user_id'),
                'client_system_id' => $this->editingSystemId,
                'event_type' => 'client_system_updated',
                'action' => 'update_client_system',
                'description' => "Updated client system: {$this->editName}",
                'details' => json_encode($updateData),
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->cancelEdit();
            $this->loadClientSystems();

            $this->dispatch('$refresh');

            $this->showMessage('Client system updated successfully', 'success');

        } catch (\Exception $e) {
            $this->showMessage('Failed to update client system: ' . $e->getMessage(), 'error');
        }
    }

    public function toggleSystemStatus($systemId)
    {
        if ($this->processing) {
            return;
        }

        $this->processing = true;

        try {
            $system = collect($this->clientSystems)->firstWhere('id', $systemId);
            $newIsActive = !$system['is_active'];
            $statusText = $newIsActive ? 'activated' : 'deactivated';

            DB::table('cas_admin.client_systems')
                ->where('id', $systemId)
                ->update([
                    'is_active' => $newIsActive,
                    'updated_at' => now()
                ]);

            DB::table('cas_audit.audit_logs')->insert([
                'user_id' => session('user_id'),
                'client_system_id' => $systemId,
                'event_type' => 'client_system_status_changed',
                'action' => 'toggle_status',
                'description' => "Client system {$statusText}: {$system['name']}",
                'details' => json_encode([
                    'previous_status' => $system['is_active'],
                    'new_status' => $newIsActive,
                    'changed_at' => now()
                ]),
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->loadClientSystems();

            $this->dispatch('$refresh');

            $this->showMessage("Client system {$statusText}", 'success');

        } catch (\Exception $e) {
            $this->showMessage('Failed to toggle status: ' . $e->getMessage(), 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function startRegenerate($systemId)
    {
        $system = collect($this->clientSystems)->firstWhere('id', $systemId);

        if ($system) {
            $this->regenerateSystemId = $systemId;
            $this->regenerateSystemName = $system['name'];
            $this->showRegenerateModal = true;
        }
    }

    public function regenerateCredentials()
    {
        $this->validate([
            'regenerateReason' => 'required|string|max:500',
        ]);

        try {
            $clientSystem = DB::table('cas_admin.client_systems')
                ->find($this->regenerateSystemId);

            if (!$clientSystem) {
                $this->showMessage('Client system not found', 'error');
                return;
            }

            $newClientSecret = bin2hex(random_bytes(32));
            $newWebhookSecret = bin2hex(random_bytes(32));

            DB::table('cas_admin.client_systems')
                ->where('id', $this->regenerateSystemId)
                ->update([
                    'client_secret' => $newClientSecret,
                    'webhook_secret' => $newWebhookSecret,
                    'credentials_shown' => false,
                    'credentials_regenerated_at' => now(),
                    'credentials_regenerated_by' => (int)session('user_id'),
                    'updated_at' => now()
                ]);

            DB::table('cas_user.sso_tokens')
                ->where('client_system_id', $this->regenerateSystemId)
                ->update(['is_active' => false]);

            DB::table('cas_audit.audit_logs')->insert([
                'user_id' => session('user_id'),
                'client_system_id' => $this->regenerateSystemId,
                'event_type' => 'critical_security_event',
                'action' => 'credentials_regenerated',
                'description' => "Client system credentials regenerated: {$clientSystem->name}",
                'details' => json_encode([
                    'reason' => $this->regenerateReason,
                    'regenerated_at' => now(),
                    'all_tokens_invalidated' => true,
                    'old_client_id' => $clientSystem->client_id
                ]),
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->newCredentials = [
                'client_id' => $clientSystem->client_id,
                'client_secret' => $newClientSecret,
                'webhook_secret' => $newWebhookSecret
            ];

            $this->createdSystemName = $clientSystem->name;

            $this->cancelRegenerate();
            $this->showCredentials = true;

            $this->loadClientSystems();

            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            $this->showMessage('Failed to regenerate credentials: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteClientSystem($systemId)
    {
        if ($this->processing) {
            return;
        }

        $this->processing = true;

        try {
            $system = collect($this->clientSystems)->firstWhere('id', $systemId);

            if (!$system) {
                $this->showMessage('Client system not found', 'error');
                return;
            }

            DB::table('cas_audit.audit_logs')->insert([
                'user_id' => session('user_id'),
                'client_system_id' => $systemId,
                'event_type' => 'client_system_deleted',
                'action' => 'delete_client_system',
                'description' => "Deleted client system: {$system['name']}",
                'details' => json_encode([
                    'deleted_system' => $system
                ]),
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('cas_admin.client_systems')
                ->where('id', $systemId)
                ->delete();

            $this->loadClientSystems();

            $this->dispatch('$refresh');

            $this->showMessage('Client system deleted successfully', 'success');

        } catch (\Exception $e) {
            $this->showMessage('Failed to delete client system: ' . $e->getMessage(), 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function resetCreateForm()
    {
        $this->name = '';
        $this->description = '';
        $this->callback_url = '';
    }

    public function cancelCreate()
    {
        $this->showCreateForm = false;
        $this->resetCreateForm();
    }

    public function cancelEdit()
    {
        $this->editingSystemId = null;
        $this->editName = '';
        $this->editDomain = '';
        $this->editCallbackUrl = '';
        $this->editStatus = '';
    }

    public function cancelRegenerate()
    {
        $this->showRegenerateModal = false;
        $this->regenerateSystemId = null;
        $this->regenerateReason = '';
        $this->regenerateSystemName = '';
    }

    public function showMessage($message, $type = 'success')
    {
        $this->message = $message;
        $this->messageType = $type;

        $this->dispatch('hide-message');
    }

    public function hideMessage()
    {
        $this->message = '';
    }

    public function render()
    {
        return view('admin.livewire.client-systems-manager');
    }
}
