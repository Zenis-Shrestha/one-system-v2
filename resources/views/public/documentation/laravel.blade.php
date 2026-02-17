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
            <span><i class="fas fa-clock mr-1"></i>Setup time: 2 minutes</span>
            <span><i class="fas fa-code mr-1"></i>Difficulty: Easy</span>
            <span><i class="fas fa-tag mr-1"></i>Laravel 10 / 11+</span>
        </div>
    </div>

    <!-- Table of Contents -->
    <div class="bg-gray-50 rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold mb-4">Table of Contents</h2>
        <nav class="space-y-2">
            <a href="#installation" class="block text-blue-600 hover:text-blue-800">1. Installation</a>
            <a href="#configuration" class="block text-blue-600 hover:text-blue-800">2. Configuration</a>
            <a href="#model-setup" class="block text-blue-600 hover:text-blue-800">3. User Model Setup</a>
            <a href="#middleware" class="block text-blue-600 hover:text-blue-800">4. Middleware & Routes</a>
        </nav>
    </div>

    <!-- Installation -->
    <section id="installation" class="mb-12">
        <h2 class="text-2xl font-bold mb-4">1. Installation</h2>
        
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Streamlined Setup:</strong> The improved package includes an auto-installer command to handle most setup steps for you.
                    </p>
                </div>
            </div>
        </div>

        <h3 class="text-xl font-semibold mb-3">Option A: Automated Installation (Recommended)</h3>
        
        <div class="space-y-4">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-black rounded-full flex items-center justify-center mr-4 mt-1">
                    <span class="text-white font-semibold text-sm">1</span>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Require the Package</h4>
                    <div class="code-block mb-3">
                        <pre class="language-bash"><code>composer require cas-system/laravel-client</code></pre>
                    </div>
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-black rounded-full flex items-center justify-center mr-4 mt-1">
                    <span class="text-white font-semibold text-sm">2</span>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Run the Installer</h4>
                    <p class="text-gray-600 mb-3">This command publishes config, runs migrations, and updates your User model and .env file.</p>
                    <div class="code-block mb-3">
                        <pre class="language-bash"><code>php artisan cas:install</code></pre>
                    </div>
                </div>
            </div>
            
            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-black rounded-full flex items-center justify-center mr-4 mt-1">
                    <span class="text-white font-semibold text-sm">3</span>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Migrate Database</h4>
                    <div class="code-block mb-3">
                        <pre class="language-bash"><code>php artisan migrate</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Configuration -->
    <section id="configuration" class="mb-12">
        <h2 class="text-2xl font-bold mb-4">2. Configuration</h2>
        
        <p class="text-gray-700 mb-6">The installer automatically updates your <code class="bg-gray-100 px-2 py-1 rounded">.env</code> file. Ensure these variables are set:</p>
        
        <div class="code-block mb-6">
            <pre class="language-env"><code># CAS Connection
CAS_SERVER_URL=http://your-one-system-url
CAS_CLIENT_ID=your_client_id
CAS_CLIENT_SECRET=your_client_secret

# User Management
CAS_CREATE_LOCAL_USERS=true  <-- Set to false to prevent auto-creation of users
CAS_USER_DASHBOARD=/home     <-- Where to redirect after login
</code></pre>
        </div>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <p class="text-sm text-yellow-700">
                <strong>CSRF Note:</strong> The credential validation route (<code>/auth/validate</code>) handles CSRF exclusion automatically. You do <strong>not</strong> need to manually update your <code>bootstrap/app.php</code>.
            </p>
        </div>
    </section>

    <!-- User Model Setup -->
    <section id="model-setup" class="mb-12">
        <h2 class="text-2xl font-bold mb-4">3. User Model Setup</h2>
        
        <p class="text-gray-700 mb-4">The package uses a Trait to handle CAS fields. If you ran the installer, this is likely already done. If not, add it manually:</p>

        <div class="code-block mb-6">
            <pre class="language-php"><code>use CasSystem\LaravelClient\Traits\CasUserTrait;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use CasUserTrait; // <-- Adds CAS fields and handles default attributes

    // ...
}</code></pre>
        <p class="text-gray-600 mt-2 text-sm">
            <strong>Note:</strong> You can define default user attributes (like <code>user_type</code>) in your <code>config/cas-client.php</code> under <code>user.defaults</code>. The trait will automatically apply them.
        </p>
        </div>
    </section>

    <!-- Middleware -->
    <section id="middleware" class="mb-12">
        <h2 class="text-2xl font-bold mb-4">4. Middleware & Routes</h2>
        
        <h3 class="text-xl font-semibold mb-3">protecting Routes</h3>
        <p class="text-gray-700 mb-4">The package automatically registers `cas.auth` and `cas.role` middleware aliases. Use them directly in your routes:</p>
        
        <div class="code-block mb-6">
            <pre class="language-php"><code>// In routes/web.php

// Protect a group of routes
Route::middleware(['cas.auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
});

// Protect with Role
Route::middleware(['cas.auth', 'cas.role:admin'])->group(function () {
    Route::get('/admin/settings', [AdminController::class, 'index']);
});</code></pre>
        </div>

        <h3 class="text-xl font-semibold mb-3">Available Routes</h3>
        <ul class="list-disc ml-6 space-y-2 text-gray-700">
            <li><strong>Login:</strong> <code>/cas/login</code> (Redirects to One System)</li>
            <li><strong>Logout:</strong> <code>/cas/logout</code> (Logs out locally and from One System)</li>
            <li><strong>Callback:</strong> <code>/cas/callback</code> (Handles the return token)</li>
            <li><strong>Validation:</strong> <code>/auth/validate</code> (Internal API for One System)</li>
        </ul>
    </section>

    <!-- Next Steps -->
    <div class="bg-blue-50 rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Need Help?</h2>
        <ul class="space-y-2">
            <li>• Ensure your `CAS_SERVER_URL` is reachable from your application.</li>
            <li>• Check `storage/logs/laravel.log` for any CAS-related errors.</li>
            <li>• Verify your Client ID and Secret in the One System Admin Dashboard.</li>
        </ul>
    </div>
</div>
@endsection