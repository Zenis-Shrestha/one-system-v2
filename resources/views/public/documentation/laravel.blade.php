@extends('public.documentation.layout')

@section('title', 'Laravel Integration — CAS SSO')
@section('description', 'Complete guide for integrating Laravel applications with CAS Single Sign-On authentication.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fab fa-laravel text-red-600 text-lg"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-blue-600 tracking-wide uppercase">Integration Guide</p>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">Laravel</h1>
            </div>
        </div>
        <p class="text-lg text-slate-500 leading-relaxed mb-4">{{ $laravelGuide['description'] }}</p>
        <div class="flex flex-wrap gap-4 text-xs text-slate-500">
            <span><i class="fas fa-clock mr-1"></i>2 min setup</span>
            <span><i class="fas fa-signal mr-1"></i>Easy</span>
            <span><i class="fas fa-tag mr-1"></i>Laravel 10 / 11+</span>
        </div>
    </div>
</section>

{{-- TOC --}}
<nav class="mb-12 p-5 rounded-xl border border-slate-200 bg-slate-50/50">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">On This Page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#installation" class="text-blue-600 hover:text-blue-800">1. Installation</a></li>
        <li><a href="#configuration" class="text-blue-600 hover:text-blue-800">2. Configuration</a></li>
        <li><a href="#model-setup" class="text-blue-600 hover:text-blue-800">3. User Model Setup</a></li>
        <li><a href="#middleware" class="text-blue-600 hover:text-blue-800">4. Middleware &amp; Routes</a></li>
    </ol>
</nav>

{{-- Installation --}}
<section id="installation" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">1. Installation</h2>
    <p class="text-sm text-slate-600 leading-relaxed mb-4">Install the CAS client package via Composer:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">Terminal</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>composer require cas-system/laravel-client</code></pre>
        </div>
    </div>
    <p class="text-sm text-slate-600 leading-relaxed">After installation, publish the configuration file:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mt-4">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>php artisan vendor:publish --tag=cas-config</code></pre>
        </div>
    </div>
</section>

{{-- Configuration --}}
<section id="configuration" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">2. Configuration</h2>
    <p class="text-sm text-slate-600 leading-relaxed mb-4">Add the following variables to your <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono">.env</code> file:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">.env</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-amber-300">CAS_SERVER_URL</span>=<span class="text-green-400">https://cas.muninfosys.com</span>
<span class="text-amber-300">CAS_CLIENT_ID</span>=<span class="text-green-400">your_client_id</span>
<span class="text-amber-300">CAS_CLIENT_SECRET</span>=<span class="text-green-400">your_client_secret</span>
<span class="text-amber-300">CAS_CALLBACK_URL</span>=<span class="text-green-400">https://your-app.com/cas/callback</span></code></pre>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">config/cas-client.php</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-violet-400">return</span> [
    <span class="text-amber-300">'server_url'</span>      => <span class="text-green-400">env</span>(<span class="text-amber-300">'CAS_SERVER_URL'</span>),
    <span class="text-amber-300">'client_id'</span>       => <span class="text-green-400">env</span>(<span class="text-amber-300">'CAS_CLIENT_ID'</span>),
    <span class="text-amber-300">'client_secret'</span>   => <span class="text-green-400">env</span>(<span class="text-amber-300">'CAS_CLIENT_SECRET'</span>),
    <span class="text-amber-300">'callback_url'</span>    => <span class="text-green-400">env</span>(<span class="text-amber-300">'CAS_CALLBACK_URL'</span>),

    <span class="text-slate-500">// Security</span>
    <span class="text-amber-300">'enable_signature_validation'</span> => <span class="text-blue-400">true</span>,
    <span class="text-amber-300">'verify_ssl'</span>  => <span class="text-blue-400">true</span>,
    <span class="text-amber-300">'timeout'</span>     => <span class="text-blue-400">30</span>,

    <span class="text-slate-500">// Routes</span>
    <span class="text-amber-300">'routes'</span> => [
        <span class="text-amber-300">'enabled'</span>    => <span class="text-blue-400">true</span>,
        <span class="text-amber-300">'prefix'</span>     => <span class="text-green-400">'cas'</span>,
        <span class="text-amber-300">'middleware'</span>  => [<span class="text-green-400">'web'</span>],
    ],

    <span class="text-slate-500">// User management</span>
    <span class="text-amber-300">'user'</span> => [
        <span class="text-amber-300">'create_local_users'</span> => <span class="text-blue-400">true</span>,
        <span class="text-amber-300">'model'</span> => <span class="text-green-400">'App\Models\User'</span>,
    ],

    <span class="text-slate-500">// Cache</span>
    <span class="text-amber-300">'cache'</span> => [
        <span class="text-amber-300">'enabled'</span> => <span class="text-blue-400">true</span>,
        <span class="text-amber-300">'ttl'</span>     => <span class="text-blue-400">3600</span>,
    ],
];</code></pre>
        </div>
    </div>
</section>

{{-- Model Setup --}}
<section id="model-setup" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">3. User Model Setup</h2>
    <p class="text-sm text-slate-600 leading-relaxed mb-4">Add the <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono">HasCasAuth</code> trait to your User model:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">app/Models/User.php</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-violet-400">use</span> CasSystem\Traits\<span class="text-orange-300">HasCasAuth</span>;

<span class="text-violet-400">class</span> <span class="text-orange-300">User</span> <span class="text-violet-400">extends</span> <span class="text-orange-300">Authenticatable</span>
{
    <span class="text-violet-400">use</span> <span class="text-orange-300">HasCasAuth</span>;

    <span class="text-violet-400">protected</span> <span class="text-red-300">$fillable</span> = [
        <span class="text-amber-300">'name'</span>, <span class="text-amber-300">'email'</span>, <span class="text-amber-300">'cas_id'</span>, <span class="text-amber-300">'cas_token'</span>,
    ];
}</code></pre>
        </div>
    </div>
</section>

{{-- Middleware & Routes --}}
<section id="middleware" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">4. Middleware &amp; Routes</h2>
    <p class="text-sm text-slate-600 leading-relaxed mb-4">Register the CAS middleware in your application:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">bootstrap/app.php (Laravel 11)</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-red-300">$middleware</span>-><span class="text-green-400">alias</span>([
    <span class="text-amber-300">'cas.auth'</span> => \CasSystem\Middleware\<span class="text-orange-300">CasAuthenticate</span>::<span class="text-blue-400">class</span>,
]);</code></pre>
        </div>
    </div>

    <p class="text-sm text-slate-600 leading-relaxed mb-4">Protect your routes:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">routes/web.php</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-violet-400">Route</span>::<span class="text-green-400">middleware</span>([<span class="text-amber-300">'cas.auth'</span>])-><span class="text-green-400">group</span>(<span class="text-blue-400">function</span> () {
    <span class="text-violet-400">Route</span>::<span class="text-green-400">get</span>(<span class="text-amber-300">'/dashboard'</span>, [<span class="text-orange-300">DashboardController</span>::<span class="text-blue-400">class</span>, <span class="text-amber-300">'index'</span>]);
    <span class="text-violet-400">Route</span>::<span class="text-green-400">get</span>(<span class="text-amber-300">'/profile'</span>,   [<span class="text-orange-300">ProfileController</span>::<span class="text-blue-400">class</span>, <span class="text-amber-300">'show'</span>]);
});

<span class="text-slate-500">// CAS callback route (handled by the package if routes.enabled = true)</span>
<span class="text-violet-400">Route</span>::<span class="text-green-400">get</span>(<span class="text-amber-300">'/cas/callback'</span>, [<span class="text-orange-300">CasController</span>::<span class="text-blue-400">class</span>, <span class="text-amber-300">'callback'</span>]);

<span class="text-slate-500">// Access the authenticated CAS user</span>
<span class="text-red-300">$user</span> = <span class="text-green-400">session</span>(<span class="text-amber-300">'cas_user'</span>);</code></pre>
        </div>
    </div>

    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="text-sm text-emerald-800">
                <strong>Done!</strong> Your Laravel application is now connected to CAS SSO. Users will be redirected to the CAS login page and returned to your app with an authenticated session.
            </div>
        </div>
    </div>
</section>

{{-- Next Steps --}}
<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Next Steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-code text-slate-400 text-sm"></i>
            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">API Reference</span>
        </a>
        <a href="{{ route('docs.security') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-shield-alt text-slate-400 text-sm"></i>
            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Security Guide</span>
        </a>
        <a href="{{ route('docs.examples') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-play text-slate-400 text-sm"></i>
            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Code Examples</span>
        </a>
    </div>
</section>
@endsection