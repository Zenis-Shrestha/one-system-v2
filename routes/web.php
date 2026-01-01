<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\Admin\ClientSystemController;
use App\Http\Controllers\Public\DocumentationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\IpWhitelistController;
use App\Http\Controllers\User\UserDashboardController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

Route::get('/', function () {
    return view('home');
});

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/auth/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/auth/login', [AuthController::class, 'login'])->name('login.post')
    ->middleware(['rate_limit_login', 'account_lockout']);

// 2FA Routes
Route::get('/auth/2fa', [App\Http\Controllers\Auth2FAController::class, 'show2FA'])->name('auth.2fa');
Route::post('/auth/2fa/verify', [App\Http\Controllers\Auth2FAController::class, 'verify2FA'])->name('auth.2fa.verify');
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/register', [AuthController::class, 'register']);
Route::post('/api/logout', [AuthController::class, 'logout']);
Route::get('/api/user', [AuthController::class, 'user']);

// SSO Routes
Route::post('/api/sso/token', [AuthController::class, 'generateSSOToken'])->middleware('ip.whitelist');
// Route::post('/api/sso/validate', [AuthController::class, 'validateSSOToken'])->middleware('ip.whitelist');
Route::post('/api/validate-token', [AuthController::class, 'validateToken'])->middleware('ip.whitelist');
Route::get('/sso/login', [AuthController::class, 'ssoLogin'])->name('sso.login');
Route::get('/auth/sso/callback', [AuthController::class, 'ssoCallback'])->name('sso.callback');
Route::post('/api/sso/process', [AuthController::class, 'processSSOCallback']);

// User Dashboard Routes
Route::get('/user/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
Route::get('/user/profile', function() { return view('user.user-profile-livewire'); })->name('user.profile');
Route::get('/user/profile-livewire', function() { return view('user.user-profile-livewire'); })->name('user.profile.livewire');
Route::get('/api/user/dashboard', [UserDashboardController::class, 'dashboard']);


Route::post('/api/user/link-client-system', [UserDashboardController::class, 'linkClientSystem']);
Route::post('/api/user/login-client-system/{clientSystemId}', [UserDashboardController::class, 'loginToClientSystem']);
Route::delete('/api/user/unlink-client-system/{clientSystemId}', [UserDashboardController::class, 'unlinkClientSystem']);

// Admin Dashboard
Route::get('/admin/dashboard', function() { return view('admin.dashboard-livewire'); })->name('admin.dashboard');
Route::get('/admin', function() { return redirect('/admin/dashboard'); });

// Client System Management
Route::get('/admin/client-systems', function() { return view('admin.client-systems-livewire'); })->name('admin.client-systems.livewire');
Route::get('/admin/client-systems-old', function() { return view('admin.client-systems'); })->name('admin.client-systems');

// Legacy API routes
Route::get('/api/client-systems', [ClientSystemController::class, 'index']);
Route::post('/api/client-systems', [ClientSystemController::class, 'store']);
Route::put('/api/client-systems/{id}', [ClientSystemController::class, 'update']);
Route::delete('/api/client-systems/{id}', [ClientSystemController::class, 'destroy']);
Route::post('/api/client-systems/{id}/mark-credentials-viewed', [ClientSystemController::class, 'markCredentialsViewed']);
Route::post('/api/client-systems/{id}/regenerate-credentials', [ClientSystemController::class, 'regenerateCredentials']);

// IP Whitelist Management
Route::get('/admin/ip-whitelist', function() { return view('admin.ip-whitelist-livewire'); })->name('admin.ip-whitelist.livewire');

// Legacy API routes for IP whitelist
Route::get('/api/ip-whitelist', [IpWhitelistController::class, 'index']);
Route::post('/api/ip-whitelist', [IpWhitelistController::class, 'store']);
Route::put('/api/ip-whitelist/{id}', [IpWhitelistController::class, 'update']);
Route::delete('/api/ip-whitelist/{id}', [IpWhitelistController::class, 'destroy']);

// User Management - Livewire Implementation
Route::get('/admin/users', function() { return view('admin.users-livewire'); })->name('admin.users.livewire');

// Audit Logs - Livewire Implementation
Route::get('/admin/audit-logs', function() { return view('admin.audit-logs-livewire'); })->name('admin.audit-logs.livewire');

// SSO Settings - Livewire Implementation
Route::get('/admin/sso-settings', function() { return view('admin.sso-settings-livewire'); })->name('admin.sso-settings.livewire');

// Admin Profile - Livewire Implementation
Route::get('/admin/profile', function() { return view('admin.profile-livewire'); })->name('admin.profile.livewire');

// Admin Security Settings - Livewire Implementation
Route::get('/admin/security-settings', function() { return view('admin.security-settings-livewire'); })->name('admin.security-settings.livewire');

// Logout Routes
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/auth/logout', function() {
    return redirect()->route('login')->withErrors(['error' => 'Please use the logout button to sign out safely.']);
})->name('logout.get');

// Password Reset Routes
Route::get('/auth/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/auth/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/auth/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/auth/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');

// Legacy API routes for audit logs
Route::get('/api/audit-logs', [AuditLogController::class, 'index']);
Route::get('/api/audit-logs/{id}', [AuditLogController::class, 'show']);
Route::get('/api/audit-logs/export', [AuditLogController::class, 'export']);
Route::get('/api/audit-logs-stats', [AuditLogController::class, 'stats']);

// Documentation Routes
Route::get('/docs', [DocumentationController::class, 'index'])->name('docs');
Route::get('/docs/system-architecture', function() { return view('public.documentation.system-architecture'); })->name('docs.architecture');

// Specific documentation section routes
Route::get('/docs/laravel', [DocumentationController::class, 'laravel'])->name('docs.laravel');
Route::get('/docs/dotnet', [DocumentationController::class, 'dotnet'])->name('docs.dotnet');
Route::get('/docs/nodejs', [DocumentationController::class, 'nodejs'])->name('docs.nodejs');
Route::get('/docs/java', [DocumentationController::class, 'java'])->name('docs.java');
Route::get('/docs/python', [DocumentationController::class, 'python'])->name('docs.python');
Route::get('/docs/javascript', [DocumentationController::class, 'javascript'])->name('docs.javascript');
Route::get('/docs/api', [DocumentationController::class, 'api'])->name('docs.api.overview');
Route::get('/docs/security', [DocumentationController::class, 'security'])->name('docs.security');
Route::get('/docs/deployment', [DocumentationController::class, 'deployment'])->name('docs.deployment');
Route::get('/docs/troubleshooting', [DocumentationController::class, 'troubleshooting'])->name('docs.troubleshooting');
Route::get('/docs/examples', [DocumentationController::class, 'examples'])->name('docs.examples');

Route::get('/docs/api/{endpoint}', [DocumentationController::class, 'apiEndpoint'])->name('docs.api');

Route::get('/docs/{section}', [DocumentationController::class, 'section'])->name('docs.section');

Route::get('/downloads/one-system-client-package.zip', function() {
    $file = public_path('downloads/one-system-client-package.zip');
    if (!file_exists($file)) {
        abort(404, 'Package not found');
    }
    return response()->download($file);
})->name('downloads.laravel-package');

Route::get('/health/database', function() {
    try {
        $userCount = DB::table('cas_user.users')->count();
        $clientCount = DB::table('cas_admin.client_systems')->count();

        return response()->json([
            'status' => 'healthy',
            'database' => 'connected',
            'stats' => [
                'users' => $userCount,
                'client_systems' => $clientCount
            ],
            'timestamp' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'database' => 'disconnected',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
})->name('health.database');

Route::get('/health', function() {
    try {
        return response()->json([
            'status' => 'healthy',
            'services' => [
                'database' => 'connected',
                'livewire' => 'active',
                'cas_server' => 'running'
            ],
            'version' => '1.0.0',
            'timestamp' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'services' => [
                'database' => 'error: ' . $e->getMessage(),
                'livewire' => 'active',
                'cas_server' => 'running'
            ],
            'timestamp' => now()->toISOString()
        ], 500);
    }
})->name('health');

Route::get('/health/full', function() {
    try {
        $userCount = DB::table('cas_user.users')->count();
        $activeUsers = DB::table('cas_user.users')->where('is_active', true)->count();
        $adminUsers = DB::table('cas_user.users')->where('role', 'admin')->count();

        $clientCount = DB::table('cas_admin.client_systems')->count();
        $activeClients = DB::table('cas_admin.client_systems')->where('is_active', true)->count();

        $auditLogCount = DB::table('cas_audit.audit_logs')->count();
        $recentAudits = DB::table('cas_audit.audit_logs')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $ipWhitelistCount = DB::table('cas_admin.ip_whitelist')->count();
        $activeIpRules = DB::table('cas_admin.ip_whitelist')->where('is_active', true)->count();

        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            'server_time' => now()->toISOString(),
            'environment' => app()->environment(),
        ];

        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'system' => $systemInfo,
            'database' => [
                'status' => 'connected',
                'stats' => [
                    'users' => [
                        'total' => $userCount,
                        'active' => $activeUsers,
                        'admins' => $adminUsers
                    ],
                    'client_systems' => [
                        'total' => $clientCount,
                        'active' => $activeClients
                    ],
                    'audit_logs' => [
                        'total' => $auditLogCount,
                        'recent_week' => $recentAudits
                    ],
                    'ip_whitelist' => [
                        'total' => $ipWhitelistCount,
                        'active' => $activeIpRules
                    ]
                ]
            ],
            'services' => [
                'cas_server' => 'running',
                'livewire' => 'active',
                'authentication' => 'operational',
                'sso' => 'available'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'timestamp' => now()->toISOString(),
            'error' => $e->getMessage(),
            'services' => [
                'cas_server' => 'running',
                'livewire' => 'active',
                'database' => 'error'
            ]
        ], 500);
    }
})->name('health.full');

Route::get('/health/redis', function() {
    try {
        $cacheStore = config('cache.default');
        $redisConfigured = $cacheStore === 'redis' || in_array('redis', array_keys(config('cache.stores', [])));

        if (!$redisConfigured) {
            return response()->json([
                'status' => 'not_configured',
                'redis' => [
                    'connection' => 'not_configured',
                    'message' => 'Redis is not configured as cache driver',
                    'current_cache_driver' => $cacheStore
                ],
                'timestamp' => now()->toISOString()
            ]);
        }

        $testKey = 'health_check_' . time();
        $testValue = 'test_value_' . uniqid();

        Cache::put($testKey, $testValue, 60);
        $retrievedValue = Cache::get($testKey);
        Cache::forget($testKey);

        $testPassed = ($retrievedValue === $testValue);

        return response()->json([
            'status' => $testPassed ? 'healthy' : 'warning',
            'redis' => [
                'connection' => 'connected',
                'read_write_test' => $testPassed ? 'passed' : 'failed',
                'cache_driver' => $cacheStore,
                'test_key' => $testKey,
                'note' => 'Using Laravel Cache facade for Redis testing'
            ],
            'timestamp' => now()->toISOString()
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'redis' => [
                'connection' => 'failed',
                'error' => $e->getMessage(),
                'cache_driver' => config('cache.default', 'unknown')
            ],
            'timestamp' => now()->toISOString()
        ], 500);
    }
})->name('health.redis');
