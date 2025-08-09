<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ClientSystem;
use App\Models\UserClientSystem;

class UserDashboardController extends Controller
{
    /**
     * Display the user dashboard view
     */
    public function index()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['error' => 'Please log in to access the dashboard']);
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget(['user_id', 'username', 'role']);
            return redirect()->route('login')->withErrors(['error' => 'Invalid session, please log in again']);
        }

        return view('user.dashboard', compact('user'));
    }

    /**
     * Get user's dashboard with available client systems and their links
     */
    public function dashboard()
    {
        $userId = session('user_id');
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $clientSystems = ClientSystem::where('is_active', true)
                ->select('id', 'name', 'domain', 'callback_url', 'created_at')
                ->get();

            $userLinks = UserClientSystem::where('user_id', $userId)
                ->where('is_active', true)
                ->get()
                ->keyBy('client_system_id');

            $dashboardData = $clientSystems->map(function($clientSystem) use ($userLinks) {
                $link = $userLinks->get($clientSystem->id);
                $showInDashboard = $link ? ($link->show_in_dashboard ?? true) : true;

                return [
                    'client_system' => $clientSystem,
                    'is_linked' => !is_null($link),
                    'linked_username' => $link ? $link->linked_username : null,
                    'last_login' => $link ? $link->last_login : null,
                    'show_in_dashboard' => $showInDashboard,
                ];
            })->filter(function($item) {
                return $item['show_in_dashboard'];
            })->values();

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role ?? 'user'
                ],
                'client_systems' => $dashboardData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Link user to a client system with a specific username
     */
    public function linkClientSystem(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'client_system_id' => 'required|integer|exists:client_systems,id',
            'linked_username' => 'required|string|max:255',
        ]);

        try {
            $clientSystem = ClientSystem::where('id', $request->client_system_id)
                ->where('is_active', true)
                ->first();

            if (!$clientSystem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client system not found or inactive'
                ], 404);
            }

            $existingLink = UserClientLink::where('user_id', $userId)
                ->where('client_system_id', $request->client_system_id)
                ->first();

            if ($existingLink) {
                $existingLink->update([
                    'linked_username' => $request->linked_username,
                    'is_active' => true,
                ]);
            } else {
                UserClientLink::create([
                    'user_id' => $userId,
                    'client_system_id' => $request->client_system_id,
                    'linked_username' => $request->linked_username,
                    'is_active' => true,
                ]);
            }

            AuditLog::create([
                'user_id' => $userId,
                'event_type' => 'user_link',
                'action' => 'link_client_system',
                'description' => "User linked to client system: {$clientSystem->name}",
                'details' => [
                    'linked_username' => $request->linked_username,
                    'client_system_name' => $clientSystem->name,
                    'client_system_id' => $request->client_system_id
                ],
                'success' => true,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully linked to client system'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to link client system: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login to a linked client system
     */
    public function loginToClientSystem(Request $request, $clientSystemId)
    {
        $userId = session('user_id');
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $userLink = DB::table('user_client_links')
                ->where('user_id', $userId)
                ->where('client_system_id', $clientSystemId)
                ->where('is_active', true)
                ->first();

            if (!$userLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not linked to this client system'
                ], 403);
            }

            $clientSystem = DB::table('client_systems')
                ->where('id', $clientSystemId)
                ->where('is_active', true)
                ->first();

            if (!$clientSystem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client system not found or inactive'
                ], 404);
            }

            $tokenResponse = $this->generateSSOTokenForUser($userId, $clientSystem, $userLink->linked_username, $request);

            if ($tokenResponse['success']) {
                DB::table('user_client_links')
                    ->where('id', $userLink->id)
                    ->update(['last_login' => now()]);

                return response()->json([
                    'success' => true,
                    'redirect_url' => $tokenResponse['redirect_url'],
                    'message' => 'Redirecting to ' . $clientSystem->name
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $tokenResponse['message']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate SSO token for a specific user and client system
     */
    private function generateSSOTokenForUser($userId, $clientSystem, $linkedUsername, $request)
    {
        try {
            $user = DB::table('users')->find($userId);

            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            $jwtSecret = config('app.jwt_secret', env('JWT_SECRET', 'your-secret-key'));

            $payload = [
                'userId' => $user->id,
                'username' => $user->username,
                'linkedUsername' => $linkedUsername,
                'email' => $user->email,
                'role' => $user->role ?? 'user',
                'clientSystemId' => $clientSystem->id,
                'clientId' => $clientSystem->client_id,
                'iat' => time(),
                'exp' => time() + (8 * 60 * 60),
                'jti' => bin2hex(random_bytes(16)),
            ];

            $token = JWT::encode($payload, $jwtSecret, 'HS256');
            $expiresAt = date('Y-m-d H:i:s', $payload['exp']);

            DB::table('sso_tokens')->insert([
                'token' => $token,
                'user_id' => $user->id,
                'client_system_id' => $clientSystem->id,
                'expires_at' => $expiresAt,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('client_systems')
                ->where('id', $clientSystem->id)
                ->update(['last_accessed' => now()]);

            DB::table('audit_logs')->insert([
                'user_id' => $user->id,
                'client_system_id' => $clientSystem->id,
                'event_type' => 'sso_login',
                'action' => 'dashboard_login',
                'description' => "User logged into {$clientSystem->name} via dashboard",
                'details' => json_encode([
                    'linked_username' => $linkedUsername,
                    'client_system_name' => $clientSystem->name
                ]),
                'success' => true,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $redirectUrl = $clientSystem->callback_url . '?token=' . $token;

            return [
                'success' => true,
                'redirect_url' => $redirectUrl,
                'token' => $token
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Token generation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Unlink user from a client system
     */
    public function unlinkClientSystem(Request $request, $clientSystemId)
    {
        $userId = session('user_id');
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $deleted = DB::table('user_client_links')
                ->where('user_id', $userId)
                ->where('client_system_id', $clientSystemId)
                ->delete();

            if ($deleted) {
                DB::table('audit_logs')->insert([
                    'user_id' => $userId,
                    'client_system_id' => $clientSystemId,
                    'event_type' => 'user_unlink',
                    'action' => 'unlink_client_system',
                    'description' => 'User unlinked from client system',
                    'success' => true,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully unlinked from client system'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No link found to remove'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unlink: ' . $e->getMessage()
            ], 500);
        }
    }
}
