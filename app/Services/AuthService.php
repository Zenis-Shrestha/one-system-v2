<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function login($loginInput, $password, $request)
    {
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
            // Try lenient search
            $user = User::where('email', $loginInput)
                ->orWhere('username', $loginInput)
                ->where('is_active', true)
                ->first();
        }

        if ($user && $this->verifyPassword($password, $user->password)) {
            // Check for 2FA
            if ($user->two_factor_enabled && $user->two_factor_secret) {
                session([
                    'temp_user_id' => $user->id,
                    'temp_username' => $user->username,
                    'temp_role' => $user->role,
                    '2fa_required' => true
                ]);

                return ['status' => '2fa_required'];
            }

            $this->completeLogin($user, $request);

            return ['status' => 'success', 'user' => $user];
        }

        return ['status' => 'failed'];
    }

    public function completeLogin(User $user, $request)
    {
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
    }

    public function register(array $data, $request)
    {
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
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

        return $user;
    }

    public function logout($request)
    {
        $userId = auth()->id() ?? session('user_id');
        if ($userId) {
            $this->logAuditEvent($userId, 'logout', ['ip' => $request->ip()]);
        }

        Auth::logout();
        session()->flush();
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

    private function logAuditEvent($userId, $action, $details, $clientSystemId = null)
    {
        AuditLog::create([
            'user_id' => $userId,
            'client_system_id' => $clientSystemId,
            'event_type' => $action,
            'action' => $action,
            'description' => ucfirst($action) . ' event',
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'success' => true,
        ]);
    }
}
