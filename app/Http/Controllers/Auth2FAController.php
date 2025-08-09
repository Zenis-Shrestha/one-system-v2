<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UserSecurity;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;

class Auth2FAController extends Controller
{
    public function show2FA()
    {
        if (!session('2fa_required')) {
            return redirect()->route('login');
        }

        return view('auth.2fa');
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        if (!session('2fa_required') || !session('temp_user_id')) {
            return redirect()->route('login')->withErrors(['error' => 'Invalid 2FA session']);
        }

        $userId = session('temp_user_id');
        $user = User::find($userId);

        if (!$user || !$user->two_factor_enabled) {
            return redirect()->route('login')->withErrors(['error' => '2FA not enabled for this user']);
        }

        $google2fa = new Google2FA();
        $valid = false;

        if (strlen($request->code) === 6 && is_numeric($request->code)) {
            $secret = $user->two_factor_secret;
            if ($secret) {
                try {
                    $valid = $google2fa->verifyKey($secret, $request->code);
                } catch (\Exception $e) {
                    return back()->withErrors(['code' => 'Invalid verification code format']);
                }
            }
        } else {
            $backupCodes = $user->two_factor_backup_codes ?? [];
            if (in_array($request->code, $backupCodes)) {
                $valid = true;
                $backupCodes = array_diff($backupCodes, [$request->code]);
                $user->update(['two_factor_backup_codes' => array_values($backupCodes)]);
            }
        }

        if ($valid) {
            $user->update(['last_login' => now()]);

            session([
                'user_id' => session('temp_user_id'),
                'username' => session('temp_username'),
                'role' => session('temp_role')
            ]);

            session()->forget(['temp_user_id', 'temp_username', 'temp_role', '2fa_required']);

            DB::table('cas_audit.audit_logs')->insert([
                'user_id' => $user->id,
                'client_system_id' => null,
                'event_type' => 'authentication',
                'action' => '2fa_verification_success',
                'description' => "User {$user->username} completed 2FA verification",
                'details' => json_encode(['ip' => $request->ip()]),
                'success' => true,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($user->isAdmin()) {
                return redirect()->intended('/admin/client-systems');
            } else {
                return redirect()->intended('/user/dashboard');
            }
        } else {
            DB::table('cas_audit.audit_logs')->insert([
                'user_id' => $userId,
                'client_system_id' => null,
                'event_type' => 'authentication',
                'action' => '2fa_verification_failed',
                'description' => "Failed 2FA verification attempt",
                'details' => json_encode(['ip' => $request->ip()]),
                'success' => false,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->withErrors(['code' => 'Invalid verification code']);
        }
    }
}
