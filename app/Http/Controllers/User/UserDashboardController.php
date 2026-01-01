<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ClientSystem;
use App\Models\UserClientSystem;

class UserDashboardController extends Controller
{
    private $ssoService;

    public function __construct(\App\Services\SsoService $ssoService)
    {
        $this->ssoService = $ssoService;
    }

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

            $existingLink = UserClientSystem::where('user_id', $userId)
                ->where('client_system_id', $request->client_system_id)
                ->first();

            if ($existingLink) {
                $existingLink->update([
                    'linked_username' => $request->linked_username,
                    'is_active' => true,
                ]);
            } else {
                UserClientSystem::create([
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
            $userLink = UserClientSystem::where('user_id', $userId)
                ->where('client_system_id', $clientSystemId)
                ->where('is_active', true)
                ->first();

            if (!$userLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not linked to this client system'
                ], 403);
            }

            $clientSystem = ClientSystem::where('id', $clientSystemId)
                ->where('is_active', true)
                ->first();

            if (!$clientSystem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client system not found or inactive'
                ], 404);
            }

            // Use SsoService to generate token
            $user = User::find($userId);
            $tokenResponse = $this->ssoService->generateWebSsoToken($user, $clientSystem->client_id, $request);

            if ($tokenResponse['status'] === 'success') {
                $userLink->update(['last_login' => now()]);

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
     * Unlink user from a client system
     */
    public function unlinkClientSystem(Request $request, $clientSystemId)
    {
        $userId = session('user_id');
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $deleted = UserClientSystem::where('user_id', $userId)
                ->where('client_system_id', $clientSystemId)
                ->delete();

            if ($deleted) {
                AuditLog::create([
                    'user_id' => $userId,
                    'client_system_id' => $clientSystemId,
                    'event_type' => 'user_unlink',
                    'action' => 'unlink_client_system',
                    'description' => 'User unlinked from client system',
                    'success' => true,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
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
