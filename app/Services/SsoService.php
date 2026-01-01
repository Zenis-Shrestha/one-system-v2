<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ClientSystem;
use App\Models\SsoToken;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SsoService
{
    private $jwtSecret;

    public function __construct()
    {
        $this->jwtSecret = env('JWT_SECRET', 'cas-secret-key-change-in-production');
    }

    public function generateToken($clientId, $clientSecret, $username, $request)
    {
        $clientSystem = ClientSystem::where('client_id', $clientId)
            ->where('client_secret', $clientSecret)
            ->active()
            ->first();

        if (!$clientSystem) {
            $this->logFailure(null, null, 'sso_generation_failed', 'invalid_client_credentials', 'Invalid client credentials', [
                'reason' => 'Invalid client credentials',
                'client_id' => $clientId,
                'ip' => $request->ip()
            ], $request);
            return ['status' => 'error', 'code' => 401, 'message' => 'Invalid client credentials'];
        }

        $user = User::where('username', $username)
            ->active()
            ->first();

        if (!$user) {
            $this->logFailure(null, $clientSystem->id, 'sso_generation_failed', 'user_not_found', 'SSO token generation failed - user not found or inactive', [
                'reason' => 'User not found or inactive',
                'username' => $username,
                'client_id' => $clientId,
                'ip' => $request->ip()
            ], $request);
            return ['status' => 'error', 'code' => 404, 'message' => 'User not found or inactive'];
        }

        $payload = [
            'userId' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role ?? 'user',
            'clientSystemId' => $clientSystem->id,
            'clientId' => $clientSystem->client_id,
            'iat' => time(),
            'exp' => time() + (8 * 60 * 60), // 8 hours
            'jti' => bin2hex(random_bytes(16)), // Unique token ID
        ];

        $token = JWT::encode($payload, $this->jwtSecret, 'HS256');
        $expiresAt = date('Y-m-d H:i:s', $payload['exp']);

        SsoToken::create([
            'token' => $token,
            'token_hash' => hash('sha256', $token),
            'user_id' => $user->id,
            'client_system_id' => $clientSystem->id,
            'user_role' => $user->role ?? 'user',
            'expires_at' => $expiresAt,
            'is_active' => true,
            'is_used' => false,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'payload' => $payload,
        ]);

        $clientSystem->update(['last_accessed' => now()]);

        AuditLog::create([
            'user_id' => $user->id,
            'client_system_id' => $clientSystem->id,
            'event_type' => 'sso_login',
            'action' => 'generate_sso_token',
            'description' => "SSO token generated for user {$user->username} and client {$clientSystem->name}",
            'details' => [
                'client_system_name' => $clientSystem->name,
                'ip' => $request->ip()
            ],
            'success' => true,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return [
            'status' => 'success',
            'redirect_url' => $clientSystem->callback_url . '?token=' . $token,
            'token' => $token
        ];
    }

    public function generateWebSsoToken(User $user, $clientId, $request)
    {
        $clientSystem = ClientSystem::where('client_id', $clientId)
            ->active()
            ->first();

        if (!$clientSystem) {
             $this->logFailure($user->id, null, 'sso_generation_failed', 'invalid_client_id', 'Invalid client ID', [
                'reason' => 'Invalid client ID provided',
                'client_id' => $clientId,
                'ip' => $request->ip()
            ], $request);
            return ['status' => 'error', 'code' => 400, 'message' => 'Invalid client ID'];
        }

        $payload = [
            'userId' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role ?? 'user',
            'clientSystemId' => $clientSystem->id,
            'clientId' => $clientSystem->client_id,
            'iat' => time(),
            'exp' => time() + (8 * 60 * 60), // 8 hours
            'jti' => bin2hex(random_bytes(16)), // Unique token ID
        ];

        $token = JWT::encode($payload, $this->jwtSecret, 'HS256');
        $expiresAt = date('Y-m-d H:i:s', $payload['exp']);

        SsoToken::create([
            'token' => $token,
            'token_hash' => hash('sha256', $token),
            'user_id' => $user->id,
            'client_system_id' => $clientSystem->id,
            'user_role' => $user->role ?? 'user',
            'expires_at' => $expiresAt,
            'is_active' => true,
            'is_used' => false,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'payload' => $payload,
        ]);

        $clientSystem->update(['last_accessed' => now()]);

        AuditLog::create([
            'user_id' => $user->id,
            'client_system_id' => $clientSystem->id,
            'event_type' => 'sso_login',
            'action' => 'generate_web_sso_token',
            'description' => "Web SSO token generated for user {$user->username} and client {$clientSystem->name}",
            'details' => [
                'client_system_name' => $clientSystem->name,
                'ip' => $request->ip()
            ],
            'success' => true,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return [
            'status' => 'success',
            'redirect_url' => $clientSystem->callback_url . '?token=' . $token,
            'token' => $token
        ];
    }

    public function validateToken($token, $clientId, $clientSecret, $request)
    {
        $clientIp = $this->getClientIp($request);
        
        Log::info('SsoService: validateToken called', [
            'client_id' => $clientId,
            'client_ip' => $clientIp
        ]);

        $clientSystem = ClientSystem::where('client_id', $clientId)
            ->where('client_secret', $clientSecret)
            ->active()
            ->first();

        if (!$clientSystem) {
             Log::error('SsoService: Invalid client credentials', ['client_id' => $clientId]);
             throw new \Exception('Invalid client credentials', 401);
        }

        if (!$this->verifyClientSystemIp($clientSystem, $clientIp)) {
             Log::error('SsoService: IP verification failed', [
                 'client_ip' => $clientIp,
                 'allowed_domain' => $clientSystem->domain
             ]);
             throw new \Exception('IP verification failed', 403);
        }

        $clientSystem->update(['last_accessed' => now()]);

        try {
            Log::info('SsoService: Decoding JWT...');
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            Log::info('SsoService: JWT decoded successfully');
        } catch (\Exception $e) {
             Log::error('SsoService: JWT decode failed', ['error' => $e->getMessage()]);
             throw new \Exception('Invalid token', 401);
        }

        Log::info('SsoService: Looking up SsoToken in DB...');
        $ssoToken = SsoToken::where('token_hash', hash('sha256', $token))
            ->active()
            ->with(['user', 'clientSystem'])
            ->first();

        if (!$ssoToken) {
             Log::error('SsoService: Token not found or expired', ['token_hash' => hash('sha256', $token)]);
             throw new \Exception('Invalid or expired token', 401);
        }

        if ($ssoToken->is_used) {
             Log::error('SsoService: Token already used', ['token_hash' => hash('sha256', $token)]);
             throw new \Exception('Token has already been used', 401);
        }
        
        Log::info('SsoService: SsoToken found', ['id' => $ssoToken->id]);

        if ($ssoToken->client_system_id != $clientSystem->id) {
             Log::error('SsoService: Token client mismatch', [
                 'token_client_id' => $ssoToken->client_system_id,
                 'request_client_id' => $clientSystem->id
             ]);
             throw new \Exception('Token not valid for this client system', 401);
        }

        $user = $ssoToken->user;

        if (!$user) {
             Log::error('SsoService: User not found for token');
             throw new \Exception('User not found', 401);
        }
        
        Log::info('SsoService: Validation successful, returning user', ['user_id' => $user->id]);

        // Mark token as used to prevent replay attacks
        $ssoToken->update(['is_used' => true]);

        return [
            'valid' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ],
            'expires_at' => $ssoToken->expires_at,
        ];
    }

    public function processCallback($token, $request)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            $ssoToken = SsoToken::where('token_hash', hash('sha256', $token))
                ->active()
                ->with('user')
                ->first();

            if (!$ssoToken) {
                 return ['status' => 'error', 'message' => 'Invalid or expired token'];
            }

            $user = $ssoToken->user;

            if (!$user) {
                 return ['status' => 'error', 'message' => 'User not found'];
            }

            return ['status' => 'success', 'user' => $user, 'ssoToken' => $ssoToken];

        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Token processing failed'];
        }
    }

    private function logFailure($userId, $clientSystemId, $eventType, $action, $description, $details, $request)
    {
        AuditLog::create([
            'user_id' => $userId,
            'client_system_id' => $clientSystemId,
            'event_type' => $eventType,
            'action' => $action,
            'description' => $description,
            'details' => $details,
            'success' => false,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

     private function getClientIp($request): string
    {
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];

        foreach ($ipHeaders as $header) {
            $ip = $request->server($header);
            if (!empty($ip) && $ip !== 'unknown') {
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $request->ip();
    }

    private function verifyClientSystemIp($clientSystem, $clientIp): bool
    {
        try {
            $parsedUrl = parse_url($clientSystem->domain);
            $registeredHost = $parsedUrl['host'] ?? $clientSystem->domain;

            // Optimization: trust local/private IPs in debug mode
            if (config('app.debug') && $this->isLocalOrPrivateIp($clientIp)) {
                return true;
            }

             // Quick check: if the IP matches exactly
             // Note: In real production we might want to cache DNS lookups
             // We will try DNS resolution but wrapped safely
            $registeredIps = $this->resolveHostToIps($registeredHost);
            if (in_array($clientIp, $registeredIps)) {
                return true;
            }

            // Database IP whitelist check
            $whitelistEntries = DB::table('cas_admin.ip_whitelist')
                ->where('is_active', true)
                ->get();

            foreach ($whitelistEntries as $entry) {
                if ($this->matchesIpRule($clientIp, $entry->ip_address, $entry->subnet_mask)) {
                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function resolveHostToIps($hostname): array
    {
        try {
            $ips = [];
            // Basic lookup
            $ipv4 = gethostbyname($hostname);
            if ($ipv4 !== $hostname && filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ips[] = $ipv4;
            }
            // More comprehensive lookup
            $records = dns_get_record($hostname, DNS_A | DNS_AAAA);
            if ($records) {
                foreach ($records as $record) {
                    if (isset($record['ip'])) {
                        $ips[] = $record['ip'];
                    } elseif (isset($record['ipv6'])) {
                        $ips[] = $record['ipv6'];
                    }
                }
            }
            return array_unique($ips);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function isLocalOrPrivateIp($ip): bool
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return true;
        }
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false;
    }

    private function matchesIpRule(string $ip, string $whitelistIp, ?string $subnetMask): bool
    {
        if ($ip === $whitelistIp) {
            return true;
        }

        if ($subnetMask) {
            $cidr = $whitelistIp . '/' . $subnetMask;
            return $this->ipInCidr($ip, $cidr);
        }

        return false;
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);

        if (!filter_var($ip, FILTER_VALIDATE_IP) || !filter_var($subnet, FILTER_VALIDATE_IP)) {
            return false;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - (int)$mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }
}
