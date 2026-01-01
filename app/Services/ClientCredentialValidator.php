<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ClientSystem;
use App\Models\UserClientLink;
use App\Models\AuditLog;

class ClientCredentialValidator
{
    /**
     * Validate credentials against client system in real-time
     * This is the MOST SECURE approach - validate once, store encrypted
     *
     * @param string $username
     * @param string $password
     * @param int $clientSystemId
     * @param int $userId
     * @return array
     */
    public function validateAndStore($username, $password, $clientSystemId, $userId)
    {
        $clientSystem = ClientSystem::find($clientSystemId);

        if (!$clientSystem || !$clientSystem->is_active) {
            return [
                'success' => false,
                'message' => 'Client system not found or inactive'
            ];
        }

        $validationResult = $this->validateCredentialsAgainstClientSystem(
            $username,
            $password,
            $clientSystem
        );

        if (!$validationResult['success']) {
            AuditLog::create([
                'user_id' => $userId,
                'event_type' => 'credential_validation',
                'action' => 'validation_failed',
                'description' => "Failed credential validation for {$clientSystem->name}",
                'details' => [
                    'username' => $username,
                    'client_system' => $clientSystem->name,
                    'error' => $validationResult['message']
                ],
                'success' => false,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $validationResult;
        }

        try {
            $existingLink = UserClientLink::where('user_id', $userId)
                ->where('client_system_id', $clientSystemId)
                ->first();

            $linkData = [
                'linked_username' => $username,
                'encrypted_password' => encrypt($password),
                'is_active' => true,
                'last_validated' => now(),
            ];

            if ($existingLink) {
                $existingLink->update($linkData);
                $action = 'updated';
            } else {
                UserClientLink::create([
                    'user_id' => $userId,
                    'client_system_id' => $clientSystemId,
                    ...$linkData
                ]);
                $action = 'created';
            }

            AuditLog::create([
                'user_id' => $userId,
                'event_type' => 'credential_validation',
                'action' => 'validation_success',
                'description' => "Successfully validated and stored credentials for {$clientSystem->name}",
                'details' => [
                    'username' => $username,
                    'client_system' => $clientSystem->name,
                    'action' => $action
                ],
                'success' => true,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return [
                'success' => true,
                'message' => "Credentials validated and {$action} successfully"
            ];

        } catch (\Exception $e) {
            Log::error('Credential storage error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to store credentials: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate credentials directly against the client system
     * Uses multiple validation strategies based on client system type
     */
    private function validateCredentialsAgainstClientSystem($username, $password, $clientSystem)
    {
        $baseUrl = $this->extractBaseUrl($clientSystem->callback_url);

        $loginResult = $this->tryLoginEndpoint($baseUrl, $username, $password, $clientSystem);
        if ($loginResult['success']) {
            return $loginResult;
        }

        $casResult = $this->tryCasValidation($baseUrl, $username, $password, $clientSystem);
        if ($casResult['success']) {
            return $casResult;
        }

        $apiResult = $this->tryApiAuth($baseUrl, $username, $password, $clientSystem);
        if ($apiResult['success']) {
            return $apiResult;
        }

        return [
            'success' => false,
            'message' => 'Unable to validate credentials against client system'
        ];
    }

    /**
     * Try standard login endpoint
     */
    private function tryLoginEndpoint($baseUrl, $username, $password, $clientSystem)
    {
        try {
            $response = Http::timeout(10)->post("{$baseUrl}/login", [
                'username' => $username,
                'password' => $password,
                'client_validation' => true
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] === true) {
                    return ['success' => true, 'method' => 'login_endpoint'];
                }

                if ($response->status() === 302) {
                    return ['success' => true, 'method' => 'login_redirect'];
                }
            }

        } catch (\Exception $e) {
            Log::debug("Login endpoint validation failed for {$clientSystem->name}: " . $e->getMessage());
        }

        return ['success' => false];
    }

    /**
     * Try CAS-specific validation
     */
    private function tryCasValidation($baseUrl, $username, $password, $clientSystem)
    {
        try {
            $response = Http::timeout(10)->post("{$baseUrl}/auth/validate", [
                'username' => $username,
                'password' => $password,
                'validation_only' => true
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['valid']) && $data['valid'] === true) {
                    return ['success' => true, 'method' => 'cas_validation'];
                }
            }

        } catch (\Exception $e) {
            Log::debug("CAS validation failed for {$clientSystem->name}: " . $e->getMessage());
        }

        return ['success' => false];
    }

    /**
     * Try API authentication
     */
    private function tryApiAuth($baseUrl, $username, $password, $clientSystem)
    {
        try {
            $response = Http::timeout(10)->post("{$baseUrl}/api/auth", [
                'username' => $username,
                'password' => $password,
                'validate_only' => true
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['authenticated']) && $data['authenticated'] === true) {
                    return ['success' => true, 'method' => 'api_auth'];
                }
            }

        } catch (\Exception $e) {
            Log::debug("API auth validation failed for {$clientSystem->name}: " . $e->getMessage());
        }

        return ['success' => false];
    }

    /**
     * Extract base URL from callback URL
     */
    private function extractBaseUrl($callbackUrl)
    {
        $parts = parse_url($callbackUrl);
        return $parts['scheme'] . '://' . $parts['host'] .
               (isset($parts['port']) ? ':' . $parts['port'] : '');
    }

    /**
     * Re-validate stored credentials (for periodic verification)
     */
    public function revalidateStoredCredentials($userLinkId)
    {
        $userLink = UserClientLink::with('clientSystem')->find($userLinkId);

        if (!$userLink) {
            return ['success' => false, 'message' => 'User link not found'];
        }

        try {
            $decryptedPassword = decrypt($userLink->encrypted_password);

            return $this->validateCredentialsAgainstClientSystem(
                $userLink->linked_username,
                $decryptedPassword,
                $userLink->clientSystem
            );
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to decrypt or validate credentials'
            ];
        }
    }
}
