<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use App\Models\ClientSystem;
use App\Models\SsoToken;
use App\Models\AuditLog;
use App\Models\UserSecurity;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    private $jwtSecret;

    public function __construct()
    {
        $this->jwtSecret = env('JWT_SECRET', 'cas-secret-key-change-in-production');
    }

    public function showLogin()
    {
        if ($this->isUserAuthenticated()) {
            return $this->redirectToDashboard();
        }

        return view('auth.login');
    }

    /**
     * Check if user is currently authenticated
     */
    private function isUserAuthenticated()
    {
        if (Auth::check()) {
            return true;
        }

        if (session('user_id') && session('username')) {
            $user = User::where('id', session('user_id'))
                ->where('is_active', true)
                ->first();

            if ($user) {
                Auth::login($user);
                return true;
            } else {
                session()->flush();
            }
        }

        return false;
    }

    /**
     * Redirect user to appropriate dashboard based on role
     */
    private function redirectToDashboard()
    {
        $user = Auth::user() ?? User::find(session('user_id'));

        if (!$user) {
            session()->flush();
            return redirect()->route('login');
        }

        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'user':
                return redirect()->route('user.dashboard');
            default:
                return redirect()->route('user.dashboard');
        }
    }

    public function login(LoginRequest $request)
    {
        $loginInput = $request->login ?? $request->email;

        $user = null;
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $loginInput)
                ->where('is_active', true)
                ->first();
        } else {
            $user = User::where('username', $loginInput)
                ->where('is_active', true)
                ->first();
        }

        if (!$user) {
            $user = User::where('email', $loginInput)
                ->orWhere('username', $loginInput)
                ->where('is_active', true)
                ->first();
        }

        if ($user && $this->verifyPassword($request->password, $user->password)) {
            if ($user->two_factor_enabled && $user->two_factor_secret) {
                session([
                    'temp_user_id' => $user->id,
                    'temp_username' => $user->username,
                    'temp_role' => $user->role,
                    '2fa_required' => true
                ]);

                return redirect()->route('auth.2fa')->with('message', '2FA verification required');
            }

            $user->update(['last_login' => now()]);

            Auth::login($user);

            session(['user_id' => $user->id, 'username' => $user->username, 'role' => $user->role]);

            AuditLog::create([
                'user_id' => $user->id,
                'event_type' => 'login',
                'action' => 'user_login',
                'description' => "User {$user->username} logged in",
                'details' => ['ip' => $request->ip()],
                'success' => true,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role,
                        'full_name' => $user->full_name,
                    ]
                ]);
            }

            return $this->redirectToDashboard();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return back()->withErrors(['error' => 'Invalid username or password'])->withInput();
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $existingUser = User::where('username', $request->username)
            ->orWhere('email', $request->email)
            ->first();

        if ($existingUser) {
            return response()->json(['error' => 'User already exists'], 400);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'first_name' => $request->first_name ?? '',
            'last_name' => $request->last_name ?? '',
            'is_active' => true,
        ]);

        Auth::login($user);

        session(['user_id' => $user->id, 'username' => $user->username]);

        AuditLog::create([
            'user_id' => $user->id,
            'event_type' => 'register',
            'action' => 'user_registration',
            'description' => "New user {$user->username} registered",
            'details' => ['ip' => $request->ip()],
            'success' => true,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email
            ]
        ], 201);
    }

    public function logout(Request $request)
    {
        $userId = auth()->id() ?? session('user_id');
        if ($userId) {
            $this->logAuditEvent($userId, 'logout', ['ip' => $request->ip()]);
        }

        Auth::logout();

        session()->flush();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['success' => true]);
        }

        return redirect('/auth/login')->with('message', 'You have been logged out successfully.');
    }

    public function user()
    {
        $userId = auth()->id() ?? session('user_id');
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth()->user() ?? User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email
        ]);
    }

    /**
     * Generate SSO Token using Client Credentials (client_id + client_secret + username)
     * This replaces the insecure username/password authentication
     */
    public function generateSSOToken(Request $request)
    {
        $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'username' => 'required|string',
        ]);

        $clientSystem = ClientSystem::where('client_id', $request->client_id)
            ->where('client_secret', $request->client_secret)
            ->active()
            ->first();

        if (!$clientSystem) {
            AuditLog::create([
                'user_id' => null,
                'client_system_id' => null,
                'event_type' => 'sso_generation_failed',
                'action' => 'invalid_client_credentials',
                'description' => 'SSO token generation failed - invalid client credentials',
                'details' => [
                    'reason' => 'Invalid client credentials',
                    'client_id' => $request->client_id,
                    'ip' => $request->ip()
                ],
                'success' => false,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return response()->json(['error' => 'Invalid client credentials'], 401);
        }

        $user = User::where('username', $request->username)
            ->active()
            ->first();

        if (!$user) {
            AuditLog::create([
                'user_id' => null,
                'client_system_id' => $clientSystem->id,
                'event_type' => 'sso_generation_failed',
                'action' => 'user_not_found',
                'description' => 'SSO token generation failed - user not found or inactive',
                'details' => [
                    'reason' => 'User not found or inactive',
                    'username' => $request->username,
                    'client_id' => $request->client_id,
                    'ip' => $request->ip()
                ],
                'success' => false,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return response()->json(['error' => 'User not found or inactive'], 404);
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

        $redirectUrl = $clientSystem->callback_url . '?token=' . $token;

        return response()->json([
            'redirect_url' => $redirectUrl,
            'token' => $token
        ]);
    }

    /**
     * Validate SSO Token using Client Credentials (client_id + client_secret)
     * Enhanced with IP verification - ensures request comes from registered customer portal server
     */
    public function validateSSOToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        $clientIp = $this->getClientIp($request);

        try {
            $clientSystem = ClientSystem::where('client_id', $request->client_id)
                ->where('client_secret', $request->client_secret)
                ->active()
                ->first();

            if (!$clientSystem) {
                $this->logAuditEvent(null, 'sso_validation_failed', [
                    'reason' => 'Invalid client credentials',
                    'client_id' => $request->client_id,
                    'ip' => $clientIp
                ]);
                return response()->json(['error' => 'Invalid client credentials'], 401);
            }

            if (!$this->verifyClientSystemIp($clientSystem, $clientIp, $request)) {
                $this->logAuditEvent(null, 'sso_validation_failed', [
                    'reason' => 'IP verification failed - request not from registered customer portal server',
                    'client_id' => $request->client_id,
                    'client_system_domain' => $clientSystem->domain,
                    'request_ip' => $clientIp,
                    'user_agent' => $request->userAgent()
                ], $clientSystem->id);

                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'Request must originate from registered customer portal server',
                    'ip' => $clientIp
                ], 403);
            }

            $clientSystem->update(['last_accessed' => now()]);

            $this->logAuditEvent(null, 'client_system_access', [
                'client_system_name' => $clientSystem->name,
                'client_id' => $request->client_id,
                'action' => 'sso_token_validation_success',
                'ip' => $clientIp,
                'ip_verified' => true,
                'user_agent' => $request->userAgent()
            ], $clientSystem->id);

            $decoded = JWT::decode($request->token, new Key($this->jwtSecret, 'HS256'));

            $ssoToken = SsoToken::where('token_hash', hash('sha256', $request->token))
                ->active()
                ->with(['user', 'clientSystem'])
                ->first();

            if (!$ssoToken) {
                return response()->json(['error' => 'Invalid or expired token'], 401);
            }

            if ($ssoToken->client_system_id != $clientSystem->id) {
                return response()->json(['error' => 'Token not valid for this client system'], 401);
            }

            $user = $ssoToken->user;

            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            return response()->json([
                'valid' => true,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                ],
                'expires_at' => $ssoToken->expires_at,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }

    private function verifyPassword($password, $hash)
    {
        if (preg_match('/^\$2[ayb]\$/', $hash)) {
            return Hash::check($password, $hash);
        }

        return $this->verifyScryptPassword($password, $hash);
    }

    private function verifyScryptPassword($password, $hash)
    {
        $parts = explode('.', $hash);
        if (count($parts) !== 2) {
            return false;
        }

        if (!ctype_xdigit($parts[0])) {
            return false;
        }

        $storedHash = hex2bin($parts[0]);
        $salt = $parts[1];

        $hashedPassword = scrypt($password, $salt, 65536, 8, 1, 64);

        return hash_equals($storedHash, $hashedPassword);
    }

    private function scryptHash($password)
    {
        $salt = bin2hex(random_bytes(16));
        $hash = scrypt($password, $salt, 65536, 8, 1, 64);
        return bin2hex($hash) . '.' . $salt;
    }

    public function ssoCallback(Request $request)
    {
        return view('auth.sso-callback');
    }

    public function processSSOCallback(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $decoded = JWT::decode($request->token, new Key($this->jwtSecret, 'HS256'));

            $ssoToken = SsoToken::where('token_hash', hash('sha256', $request->token))
                ->active()
                ->with('user')
                ->first();

            if (!$ssoToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token'
                ], 401);
            }

            $user = $ssoToken->user;

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 401);
            }

            session(['user_id' => $user->id, 'username' => $user->username]);

            $this->logAuditEvent($user->id, 'sso_callback_success', [
                'token_id' => $ssoToken->id,
                'ip' => $request->ip()
            ], $ssoToken->client_system_id);

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'redirect_url' => '/dashboard',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token processing failed'
            ], 401);
        }
    }

    /**
     * Get the real client IP address (same logic as middleware)
     */
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

    /**
     * Verify that the request comes from the registered customer portal server
     */
    private function verifyClientSystemIp($clientSystem, $clientIp, $request): bool
    {
        try {
            $parsedUrl = parse_url($clientSystem->domain);
            $registeredHost = $parsedUrl['host'] ?? $clientSystem->domain;

            $registeredIps = $this->resolveHostToIps($registeredHost);
            if (in_array($clientIp, $registeredIps)) {
                return true;
            }

            $whitelistEntries = DB::table('cas_admin.ip_whitelist')
                ->where('is_active', true)
                ->get();

            foreach ($whitelistEntries as $entry) {
                if ($this->matchesIpRule($clientIp, $entry->ip_address, $entry->subnet_mask)) {
                    return true;
                }
            }

            if (config('app.debug') && $this->isLocalOrPrivateIp($clientIp)) {
                return true;
            }

            return false;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Resolve hostname to IP addresses
     */
    private function resolveHostToIps($hostname): array
    {
        try {
            $ips = [];

            $ipv4 = gethostbyname($hostname);
            if ($ipv4 !== $hostname && filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ips[] = $ipv4;
            }

            $records = dns_get_record($hostname, DNS_A | DNS_AAAA);
            foreach ($records as $record) {
                if (isset($record['ip'])) {
                    $ips[] = $record['ip'];
                } elseif (isset($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }

            return array_unique($ips);

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check if IP is localhost or private network
     */
    private function isLocalOrPrivateIp($ip): bool
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return true;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false;
    }

    /**
     * Check if IP matches whitelist rule (supports CIDR notation)
     */
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

    /**
     * Check if IP is in CIDR range
     */
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

    private function logAuditEvent($userId, $action, $details, $clientSystemId = null)
    {
        DB::table('cas_audit.audit_logs')->insert([
            'user_id' => $userId,
            'client_system_id' => $clientSystemId,
            'event_type' => $action,
            'action' => $action,
            'description' => ucfirst($action) . ' event',
            'details' => json_encode($details),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'success' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
