@extends('public.documentation.layout')

@section('title', 'Laravel CAS SSO Integration Guide')
@section('description', 'Complete guide for integrating Laravel applications with CAS Single Sign-On authentication system.')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <div class="bg-red-100 w-12 h-12 rounded-lg flex items-center justify-center mr-4">
                <i class="fab fa-laravel text-red-600 text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $laravelGuide['title'] }}</h1>
                <p class="text-gray-600 mt-1">{{ $laravelGuide['description'] }}</p>
            </div>
        </div>
        
        <div class="flex items-center space-x-4 text-sm text-gray-600">
            <span><i class="fas fa-clock mr-1"></i>Setup time: 5 minutes</span>
            <span><i class="fas fa-code mr-1"></i>Difficulty: Easy</span>
            <span><i class="fas fa-tag mr-1"></i>Laravel 8+</span>
        </div>
    </div>

    <!-- Table of Contents -->
    <div class="bg-gray-50 rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold mb-4">Table of Contents</h2>
        <nav class="space-y-2">
            <a href="#installation" class="block text-blue-600 hover:text-blue-800">1. Installation</a>
            <a href="#configuration" class="block text-blue-600 hover:text-blue-800">2. Configuration</a>
            <a href="#middleware" class="block text-blue-600 hover:text-blue-800">3. Middleware Setup</a>
            <a href="#routes" class="block text-blue-600 hover:text-blue-800">4. Route Protection</a>
            <a href="#examples" class="block text-blue-600 hover:text-blue-800">5. Code Examples</a>
            <a href="#advanced" class="block text-blue-600 hover:text-blue-800">6. Advanced Usage</a>
        </nav>
    </div>

    <!-- Installation -->
    <section id="installation" class="mb-12">
        <h2 class="text-2xl font-bold mb-4">1. Installation</h2>
        
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Prerequisites:</strong> Laravel 10.0+ and PHP 8.1+
                    </p>
                </div>
            </div>
        </div>

        <h3 class="text-xl font-semibold mb-3">Installation Steps</h3>
        
        <div class="space-y-4">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-black rounded-full flex items-center justify-center mr-4 mt-1">
                    <span class="text-white font-semibold text-sm">1</span>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Prepare Packages Directory</h4>
                    <p class="text-gray-600 mb-3">Create a <code>packages</code> directory in your project root and extract the client package there:</p>
                    <div class="code-block mb-3">
                        <pre class="language-bash"><code>mkdir packages
# Extract the package zip to packages/laravel-cas-client-package</code></pre>
                    </div>
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-black rounded-full flex items-center justify-center mr-4 mt-1">
                    <span class="text-white font-semibold text-sm">2</span>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Configure Composer</h4>
                    <p class="text-gray-600 mb-3">Update your <code>composer.json</code> to include the local repository path and require the package:</p>
                    <div class="code-block mb-3">
                        <pre class="language-json"><code>"repositories": [
    {
        "type": "path",
        "url": "./packages/laravel-cas-client-package"
    }
],
"require": {
    "cas-system/laravel-client": "@dev"
}</code></pre>
                    </div>
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-black rounded-full flex items-center justify-center mr-4 mt-1">
                    <span class="text-white font-semibold text-sm">3</span>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Install Dependencies</h4>
                    <p class="text-gray-600 mb-3">Run composer update to install the local package:</p>
                    <div class="code-block mb-3">
                        <pre class="language-bash"><code>composer update</code></pre>
                    </div>
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-black rounded-full flex items-center justify-center mr-4 mt-1">
                    <span class="text-white font-semibold text-sm">4</span>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Publish Configuration</h4>
                    <p class="text-gray-600 mb-3">Publish the package configuration:</p>
                    <div class="code-block mb-3">
                        <pre class="language-bash"><code>php artisan vendor:publish --tag=cas-client-config</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Configuration -->
    <section id="configuration" class="mb-12">
        <h2 class="text-2xl font-bold mb-4">2. Configuration</h2>
        
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        <strong>New Architecture:</strong> Our CAS system now uses modular Admin/User/Public separation with enhanced security features.
                    </p>
                </div>
            </div>
        </div>
        
        <p class="text-gray-700 mb-6">Configure your CAS client by updating the <code class="bg-gray-100 px-2 py-1 rounded">config/cas-client.php</code> file:</p>
        
        <div class="code-block mb-6">
            <pre class="language-php"><code>&lt;?php

return [
    /*
    |--------------------------------------------------------------------------
    | CAS Server Configuration - Updated Architecture
    |--------------------------------------------------------------------------
    */
    'server_url' => env('CAS_SERVER_URL', 'https://your-cas-server.com'),
    'client_id' => env('CAS_CLIENT_ID'),
    'client_secret' => env('CAS_CLIENT_SECRET'),
    
    /*
    |--------------------------------------------------------------------------
    | Enhanced Authentication Settings
    |--------------------------------------------------------------------------
    */
    'token_expiry' => env('CAS_TOKEN_EXPIRY', 3600), // 1 hour
    'signature_validation' => env('CAS_SIGNATURE_VALIDATION', true),
    'hmac_algorithm' => 'sha256', // HMAC-SHA256 for enhanced security
    
    /*
    |--------------------------------------------------------------------------
    | New Route Configuration - Organized Structure
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'sso_token' => '/api/sso/token',           // Enhanced client credentials flow
        'sso_validate' => '/api/sso/validate',     // Token validation endpoint
        'callback' => '/auth/sso/callback',        // SSO callback handler
        'user_dashboard' => '/user/dashboard',     // User dashboard route
        'logout' => '/auth/logout',                // Logout endpoint
    ],
    
    /*
    |--------------------------------------------------------------------------
    | PostgreSQL Schema Configuration
    |--------------------------------------------------------------------------
    */
    'database' => [
        'connection' => env('CAS_DB_CONNECTION', 'cas_system'),
        'schemas' => [
            'admin' => 'cas_admin',
            'user' => 'cas_user', 
            'public' => 'cas_public',
            'audit' => 'cas_audit'
        ]
    ],
    
    /*
    |--------------------------------------------------------------------------
    | IP Whitelist &amp; Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'ip_whitelist_enabled' => env('CAS_IP_WHITELIST', true),
        'audit_logging' => env('CAS_AUDIT_LOGGING', true),
        'rate_limiting' => env('CAS_RATE_LIMITING', true),
    ]
];</code></pre>
        </div>

        <h3 class="text-xl font-semibold mb-3">Environment Variables - Updated</h3>
        <p class="text-gray-700 mb-4">Add these variables to your <code class="bg-gray-100 px-2 py-1 rounded">.env</code> file:</p>
        
        <div class="code-block mb-6">
            <pre class="language-env"><code># CAS Configuration - Enhanced Architecture
CAS_SERVER_URL=https://your-cas-server.com
CAS_CLIENT_ID=your-client-id
CAS_CLIENT_SECRET=your-client-secret
CAS_SIGNATURE_SECRET=your-signature-verification-secret
CAS_TOKEN_EXPIRY=3600
CAS_SIGNATURE_VALIDATION=true

# Database Configuration
CAS_DB_CONNECTION=cas_system
CAS_DB_HOST=127.0.0.1
CAS_DB_PORT=5432
CAS_DB_DATABASE=cas_system
CAS_DB_USERNAME=cas_user
CAS_DB_PASSWORD=secure_password

# Security Settings
CAS_IP_WHITELIST=true
CAS_AUDIT_LOGGING=true
CAS_RATE_LIMITING=true</code></pre>
        </div>
    </section>

    <!-- Middleware -->
    <section id="middleware" class="mb-12">
        <h2 class="text-2xl font-bold mb-4">3. Middleware Setup</h2>
        
        <h3 class="text-xl font-semibold mb-3">Register Middleware</h3>
        <p class="text-gray-700 mb-4">Add the CAS middleware to your <code class="bg-gray-100 px-2 py-1 rounded">app/Http/Kernel.php</code>:</p>
        
        <div class="code-block mb-6">
            <pre class="language-php"><code>protected $routeMiddleware = [
    // ... existing middleware
    'cas.auth' => \CasSystem\LaravelClient\Middleware\CasAuth::class,
    'cas.role' => \CasSystem\LaravelClient\Middleware\CasRole::class,
];</code></pre>
        </div>

        <h3 class="text-xl font-semibold mb-3">Custom Middleware (Manual Installation)</h3>
        <p class="text-gray-700 mb-4">If you're using manual installation, create the middleware file:</p>
        
        <div class="code-block mb-6">
            <pre class="language-php"><code>&lt;?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\CAS\CasClient;

class CasAuth
{
    public function handle(Request $request, Closure $next)
    {
        $user = session('cas_user');
        $token = session('cas_token');
        
        if (!$user || !$token) {
            // Redirect to CAS login
            $loginUrl = CasClient::getLoginUrl($request->url());
            return redirect($loginUrl);
        }
        
        // Validate token if needed
        if (!CasClient::validateToken($token)) {
            session()->forget(['cas_user', 'cas_token']);
            $loginUrl = CasClient::getLoginUrl($request->url());
            return redirect($loginUrl);
        }
        
        return $next($request);
    }
}</code></pre>
        </div>
    </section>

    <!-- Routes -->
    <section id="routes" class="mb-12">
        <h2 class="text-2xl font-bold mb-4">4. Route Protection</h2>
        
        <h3 class="text-xl font-semibold mb-3">Basic Route Protection</h3>
        <div class="code-block mb-6">
            <pre class="language-php"><code>// In routes/web.php
Route::middleware(['cas.auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::get('/settings', [SettingsController::class, 'index']);
});</code></pre>
        </div>

        <h3 class="text-xl font-semibold mb-3">Role-Based Protection</h3>
        <div class="code-block mb-6">
            <pre class="language-php"><code>// Protect routes with specific roles
Route::middleware(['cas.auth', 'cas.role:admin,manager'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
    Route::get('/reports', [ReportsController::class, 'index']);
});

// Single role protection
Route::middleware(['cas.auth', 'cas.role:admin'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'users']);
});</code></pre>
        </div>

        <h3 class="text-xl font-semibold mb-3">Authentication Routes</h3>
        <div class="code-block mb-6">
            <pre class="language-php"><code>// Authentication routes (add these to your routes/web.php)
Route::get('/cas/login', function () {
    $returnUrl = request('return_url', route('dashboard'));
    $loginUrl = CasClient::getLoginUrl($returnUrl);
    return redirect($loginUrl);
})->name('cas.login');

Route::get('/cas/callback', function () {
    $token = request('token');
    $user = CasClient::validateToken($token);
    
    if ($user) {
        session(['cas_user' => $user, 'cas_token' => $token]);
        return redirect(request('return_url', route('dashboard')));
    }
    
    return redirect()->route('login')->with('error', 'Authentication failed');
})->name('cas.callback');

Route::get('/cas/logout', function () {
    session()->forget(['cas_user', 'cas_token']);
    return redirect('/');
})->name('cas.logout');</code></pre>
        </div>
    </section>

    <!-- Examples -->
    <section id="examples" class="mb-12">
        <h2 class="text-2xl font-bold mb-4">5. Code Examples</h2>
        
        <h3 class="text-xl font-semibold mb-3">Controller Example</h3>
        <div class="code-block mb-6">
            <pre class="language-php"><code>&lt;?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CasSystem\LaravelClient\Facades\CasClient;

class DashboardController extends Controller
{
    public function index()
    {
        $user = session('cas_user');
        $token = session('cas_token');
        
        // You can also get user info directly from CAS
        $userInfo = CasClient::getUserInfo($token);
        
        return view('dashboard', compact('user', 'userInfo'));
    }
    
    public function profile()
    {
        $user = session('cas_user');
        
        // Access user properties
        $username = $user['username'];
        $email = $user['email'];
        $role = $user['role'];
        $firstName = $user['first_name'];
        $lastName = $user['last_name'];
        
        return view('profile', compact('user'));
    }
}</code></pre>
        </div>


        <h3 class="text-xl font-semibold mb-3">Service Provider Example</h3>
        <div class="code-block mb-6">
            <pre class="language-php"><code>&lt;?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use CasSystem\LaravelClient\Services\CasClient;

class CasServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CasClient::class, function ($app) {
            return new CasClient([
                'server_url' => config('cas.server_url'),
                'client_id' => config('cas.client_id'),
                'client_username' => config('cas.client_username'),
                'client_password' => config('cas.client_password'),
                'signature_secret' => config('cas.signature_secret'),
            ]);
        });
    }
}</code></pre>
        </div>
    </section>

    <!-- Advanced Usage -->
    <section id="advanced" class="mb-12">
        <h2 class="text-2xl font-bold mb-4">6. Advanced Usage</h2>
        
        <h3 class="text-xl font-semibold mb-3">Custom User Model</h3>
        <div class="code-block mb-6">
            <pre class="language-php"><code>&lt;?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class CasUser extends Authenticatable
{
    protected $fillable = [
        'username', 'email', 'first_name', 'last_name', 'role'
    ];
    
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    public function hasRole($role)
    {
        return $this->role === $role;
    }
}</code></pre>
        </div>

        <h3 class="text-xl font-semibold mb-3">Token Refresh</h3>
        <div class="code-block mb-6">
            <pre class="language-php"><code>&lt;?php

namespace App\Services;

use CasSystem\LaravelClient\Services\CasClient;

class TokenRefreshService
{
    public function refreshToken($currentToken)
    {
        try {
            $newToken = CasClient::refreshToken($currentToken);
            session(['cas_token' => $newToken]);
            return $newToken;
        } catch (\Exception $e) {
            // Token refresh failed, redirect to login
            session()->forget(['cas_user', 'cas_token']);
            throw $e;
        }
    }
}</code></pre>
        </div>

        <h3 class="text-xl font-semibold mb-3">API Integration</h3>
        <div class="code-block mb-6">
            <pre class="language-php"><code>&lt;?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CasSystem\LaravelClient\Services\CasClient;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        
        try {
            $response = CasClient::authenticate($credentials);
            
            return response()->json([
                'token' => $response['token'],
                'user' => $response['user'],
                'expires_at' => $response['expires_at'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }
    }
    
    public function validateToken(Request $request)
    {
        $token = $request->bearerToken();
        
        try {
            $user = CasClient::validateToken($token);
            return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}</code></pre>
        </div>
    </section>

    <!-- Troubleshooting -->
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-4">Troubleshooting</h2>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <h3 class="text-lg font-semibold mb-2">Common Issues</h3>
            <ul class="space-y-2 text-sm">
                <li><strong>Authentication loops:</strong> Check your callback URL configuration</li>
                <li><strong>Token validation fails:</strong> Verify your signature secret</li>
                <li><strong>Session expires quickly:</strong> Increase session lifetime in config</li>
                <li><strong>CORS errors:</strong> Add your domain to CAS server's allowed origins</li>
            </ul>
        </div>
    </section>

    <!-- Next Steps -->
    <div class="bg-blue-50 rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Next Steps</h2>
        <ul class="space-y-2">
            <li>• <a href="{{ route('docs.api.overview') }}" class="text-blue-600 hover:text-blue-800">Explore the API Reference</a></li>
            <li>• <a href="{{ route('docs.examples') }}" class="text-blue-600 hover:text-blue-800">View More Examples</a></li>
            <li>• <a href="/" class="text-blue-600 hover:text-blue-800">Test with CAS Dashboard</a></li>
        </ul>
    </div>
</div>
@endsection