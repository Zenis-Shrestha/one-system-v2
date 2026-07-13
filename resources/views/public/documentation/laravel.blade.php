@extends('public.documentation.layout')

@section('title', 'Laravel integration — ONE SSO')
@section('description', 'Integrate a Laravel application with ONE single sign-on using the cas-system/laravel-client package.')

@section('content')
@php
    // Highlight palette tuned to the ONE codeblock surface (dark ink background).
    $kw  = 'color:#c7d2fe';        // keywords / accent-tinted
    $str = 'color:#a5b4fc';        // strings
    $fn  = 'color:#e2e8f0';        // functions / identifiers
    $var = 'color:#cbd5e1';        // variables
    $com = 'color:#64748b';        // comments
    $num = 'color:#93c5fd';        // numbers / literals
@endphp

{{-- Page header --}}
<section class="border-b border-[var(--color-line)] pb-10 mb-12">
    <div class="">
        <div class="flex items-center gap-3 mb-4">
            <span class="os-icon-tile os-icon-tile-ink">
                <i class="fab fa-laravel"></i>
            </span>
            <div>
                <p class="os-eyebrow">Integration guide</p>
                <h1 class="text-3xl font-bold text-[var(--color-ink)] tracking-tight leading-tight">Laravel</h1>
            </div>
        </div>
        <p class="text-lg text-[var(--color-muted)] leading-relaxed mb-5">{{ $laravelGuide['description'] }}</p>
        <div class="flex flex-wrap gap-2">
            <span class="os-badge">Composer package</span>
            <span class="os-badge">Laravel 10 / 11 / 12</span>
            <span class="os-badge">PHP 7.2 – 8.x</span>
            <span class="os-badge-accent os-badge">cas-system/laravel-client 1.0.0</span>
        </div>
    </div>
</section>

{{-- On this page --}}
<nav class="mb-12 os-card os-card-pad">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-3">On this page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#installation" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">1. {{ $laravelGuide['sections']['installation'] }}</a></li>
        <li><a href="#configuration" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">2. {{ $laravelGuide['sections']['configuration'] }}</a></li>
        <li><a href="#model-setup" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">3. User model setup</a></li>
        <li><a href="#middleware" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">4. {{ $laravelGuide['sections']['middleware'] }} &amp; {{ $laravelGuide['sections']['routes'] }}</a></li>
        <li><a href="#flow" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">5. How the SSO flow works</a></li>
        <li><a href="#api" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">6. {{ $laravelGuide['sections']['examples'] }}</a></li>
    </ol>
</nav>

{{-- 1. Installation --}}
<section id="installation" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">1. {{ $laravelGuide['sections']['installation'] }}</h2>
    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">Install the ONE CAS client via Composer. The package auto-registers its service provider, the <code class="os-code-inline">CasClient</code> facade, routes and middleware aliases through Laravel package discovery.</p>

    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>Terminal</span></div>
        <pre><code>composer require cas-system/laravel-client</code></pre>
    </div>

    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">Then run the bundled installer. It publishes the config, adds the <code class="os-code-inline">CasUserTrait</code> to your User model and seeds the required <code class="os-code-inline">.env</code> keys. It does not modify the database:</p>

    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>Terminal</span></div>
        <pre><code>php artisan cas:install
php artisan migrate</code></pre>
    </div>

    <div class="os-alert os-alert-info mb-6">
        <i class="fas fa-circle-info mt-0.5"></i>
        <div><strong class="font-semibold">Why is migration required?</strong> Laravel packages may register migrations directly with <code class="os-code-inline">loadMigrationsFrom()</code>. This package does so, and <code class="os-code-inline">php artisan migrate</code> applies its bundled migration to add four CAS fields to the host application's <code class="os-code-inline">users</code> table. No migration publishing step is needed.</div>
    </div>

    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">Prefer to configure it by hand? Publish the config file with the <code class="os-code-inline">cas-client-config</code> tag, add <code class="os-code-inline">CasUserTrait</code> to your User model, set the environment values shown below, and run the registered package migration:</p>

    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>Terminal</span></div>
        <pre><code>php artisan vendor:publish --tag=cas-client-config
php artisan migrate</code></pre>
    </div>
</section>

{{-- 2. Configuration --}}
<section id="configuration" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">2. {{ $laravelGuide['sections']['configuration'] }}</h2>
    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">Add your ONE client credentials to <code class="os-code-inline">.env</code>. The <code class="os-code-inline">client_secret</code> is shown once when you register or regenerate the client in ONE — store it server-side only, never in browser code.</p>

    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>.env</span></div>
        <pre><code><span style="{{ $var }}">CAS_SERVER_URL</span>=<span style="{{ $str }}">https://one.example.com</span>
<span style="{{ $var }}">CAS_CLIENT_ID</span>=<span style="{{ $str }}">your_client_id</span>
<span style="{{ $var }}">CAS_CLIENT_SECRET</span>=<span style="{{ $str }}">your_client_secret</span>
<span style="{{ $var }}">CAS_CALLBACK_URL</span>=<span style="{{ $str }}">https://your-app.example.com/cas/callback</span>

<span style="{{ $com }}"># Optional — security &amp; behaviour</span>
<span style="{{ $var }}">CAS_ENABLE_SIGNATURE_VALIDATION</span>=<span style="{{ $num }}">true</span>
<span style="{{ $var }}">CAS_SIGNATURE_SECRET</span>=<span style="{{ $str }}">your-shared-signature-secret</span>
<span style="{{ $var }}">CAS_VERIFY_SSL</span>=<span style="{{ $num }}">true</span>
<span style="{{ $var }}">CAS_USER_MODEL</span>=<span style="{{ $str }}">App\Models\User</span>
<span style="{{ $var }}">CAS_USER_DASHBOARD</span>=<span style="{{ $str }}">/dashboard</span></code></pre>
    </div>

    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">The published <code class="os-code-inline">config/cas-client.php</code> reads those variables and exposes the rest of the package options:</p>

    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>config/cas-client.php</span></div>
        <pre><code><span style="{{ $kw }}">return</span> [
    <span style="{{ $str }}">'server_url'</span>     => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_SERVER_URL'</span>, <span style="{{ $str }}">'http://127.0.0.1:8001'</span>),

    <span style="{{ $com }}">// Client registered in ONE — id + secret (no username/password)</span>
    <span style="{{ $str }}">'client_id'</span>     => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_CLIENT_ID'</span>),
    <span style="{{ $str }}">'client_secret'</span> => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_CLIENT_SECRET'</span>),

    <span style="{{ $com }}">// Where ONE redirects the browser after login</span>
    <span style="{{ $str }}">'callback_url'</span>  => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_CALLBACK_URL'</span>, <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'APP_URL'</span>) . <span style="{{ $str }}">'/cas/callback'</span>),

    <span style="{{ $com }}">// HMAC SHA-256 request signing (optional, must match the server)</span>
    <span style="{{ $str }}">'enable_signature_validation'</span> => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_ENABLE_SIGNATURE_VALIDATION'</span>, <span style="{{ $num }}">true</span>),
    <span style="{{ $str }}">'signature_secret'</span> => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_SIGNATURE_SECRET'</span>),
    <span style="{{ $str }}">'verify_ssl'</span>    => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_VERIFY_SSL'</span>, <span style="{{ $num }}">true</span>),
    <span style="{{ $str }}">'timeout'</span>       => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_TIMEOUT'</span>, <span style="{{ $num }}">30</span>),

    <span style="{{ $com }}">// Package routes — auto-registered under this prefix</span>
    <span style="{{ $str }}">'routes'</span> => [
        <span style="{{ $str }}">'enabled'</span>        => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_ROUTES_ENABLED'</span>, <span style="{{ $num }}">true</span>),
        <span style="{{ $str }}">'prefix'</span>         => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_ROUTES_PREFIX'</span>, <span style="{{ $str }}">'cas'</span>),
        <span style="{{ $str }}">'middleware'</span>     => [<span style="{{ $str }}">'web'</span>],
        <span style="{{ $str }}">'user_dashboard'</span> => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_USER_DASHBOARD'</span>, <span style="{{ $str }}">'/dashboard'</span>),
    ],

    <span style="{{ $com }}">// Local user provisioning</span>
    <span style="{{ $str }}">'user'</span> => [
        <span style="{{ $str }}">'create_local_users'</span> => <span style="{{ $num }}">true</span>,
        <span style="{{ $str }}">'model'</span>              => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_USER_MODEL'</span>, <span style="{{ $str }}">'App\Models\Auth\User'</span>),
        <span style="{{ $str }}">'defaults'</span>           => [<span style="{{ $str }}">'user_type'</span> => <span style="{{ $str }}">'Guest'</span>],
    ],

    <span style="{{ $com }}">// Cache validated user data to cut calls to ONE</span>
    <span style="{{ $str }}">'cache'</span> => [
        <span style="{{ $str }}">'enabled'</span> => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_CACHE_ENABLED'</span>, <span style="{{ $num }}">true</span>),
        <span style="{{ $str }}">'ttl'</span>     => <span style="{{ $fn }}">env</span>(<span style="{{ $str }}">'CAS_CACHE_TTL'</span>, <span style="{{ $num }}">3600</span>),
        <span style="{{ $str }}">'prefix'</span>  => <span style="{{ $str }}">'cas_'</span>,
    ],
];</code></pre>
    </div>
</section>

{{-- 3. User model setup --}}
<section id="model-setup" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">3. User model setup</h2>
    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">Add the <code class="os-code-inline">CasUserTrait</code> to your User model. The trait merges the CAS columns into <code class="os-code-inline">$fillable</code> and casts, so you do not edit those arrays yourself. The migration adds <code class="os-code-inline">cas_user</code>, <code class="os-code-inline">cas_username</code>, <code class="os-code-inline">cas_token</code> and <code class="os-code-inline">cas_token_expires_at</code> to the <code class="os-code-inline">users</code> table.</p>

    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>app/Models/User.php</span></div>
        <pre><code><span style="{{ $kw }}">namespace</span> <span style="{{ $fn }}">App\Models</span>;

<span style="{{ $kw }}">use</span> <span style="{{ $fn }}">Illuminate\Foundation\Auth\User</span> <span style="{{ $kw }}">as</span> <span style="{{ $fn }}">Authenticatable</span>;
<span style="{{ $kw }}">use</span> <span style="{{ $fn }}">CasSystem\LaravelClient\Traits\CasUserTrait</span>;

<span style="{{ $kw }}">class</span> <span style="{{ $fn }}">User</span> <span style="{{ $kw }}">extends</span> <span style="{{ $fn }}">Authenticatable</span>
{
    <span style="{{ $kw }}">use</span> <span style="{{ $fn }}">CasUserTrait</span>;

    <span style="{{ $com }}">// The trait already registers cas_user, cas_username,</span>
    <span style="{{ $com }}">// cas_token and cas_token_expires_at as fillable + cast.</span>
    <span style="{{ $kw }}">protected</span> <span style="{{ $var }}">$fillable</span> = [
        <span style="{{ $str }}">'name'</span>, <span style="{{ $str }}">'email'</span>, <span style="{{ $str }}">'password'</span>,
    ];
}</code></pre>
    </div>
</section>

{{-- 4. Middleware & routes --}}
<section id="middleware" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">4. {{ $laravelGuide['sections']['middleware'] }} &amp; {{ $laravelGuide['sections']['routes'] }}</h2>
    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">The service provider registers two middleware aliases automatically, so there is no need to wire them up in <code class="os-code-inline">bootstrap/app.php</code>:</p>

    <ul class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-6 space-y-1.5 list-disc pl-5">
        <li><code class="os-code-inline">cas.auth</code> &rarr; <code class="os-code-inline">CasSystem\LaravelClient\Middleware\CasAuthentication</code> — requires an authenticated CAS session.</li>
        <li><code class="os-code-inline">cas.role</code> &rarr; <code class="os-code-inline">CasSystem\LaravelClient\Middleware\CasRole</code> — requires any of the listed roles.</li>
    </ul>

    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">Apply them to your routes by alias:</p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>routes/web.php</span></div>
        <pre><code><span style="{{ $kw }}">use</span> <span style="{{ $fn }}">App\Http\Controllers\DashboardController</span>;
<span style="{{ $kw }}">use</span> <span style="{{ $fn }}">App\Http\Controllers\AdminController</span>;

<span style="{{ $com }}">// Any authenticated CAS user</span>
<span style="{{ $fn }}">Route</span>::<span style="{{ $fn }}">middleware</span>([<span style="{{ $str }}">'cas.auth'</span>])-><span style="{{ $fn }}">group</span>(<span style="{{ $kw }}">function</span> () {
    <span style="{{ $fn }}">Route</span>::<span style="{{ $fn }}">get</span>(<span style="{{ $str }}">'/dashboard'</span>, [<span style="{{ $fn }}">DashboardController</span>::<span style="{{ $kw }}">class</span>, <span style="{{ $str }}">'index'</span>]);
});

<span style="{{ $com }}">// Restrict by role — user needs ANY of admin / manager</span>
<span style="{{ $fn }}">Route</span>::<span style="{{ $fn }}">middleware</span>([<span style="{{ $str }}">'cas.auth'</span>, <span style="{{ $str }}">'cas.role:admin,manager'</span>])-><span style="{{ $fn }}">group</span>(<span style="{{ $kw }}">function</span> () {
    <span style="{{ $fn }}">Route</span>::<span style="{{ $fn }}">get</span>(<span style="{{ $str }}">'/admin'</span>, [<span style="{{ $fn }}">AdminController</span>::<span style="{{ $kw }}">class</span>, <span style="{{ $str }}">'index'</span>]);
});</code></pre>
    </div>

    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">When <code class="os-code-inline">routes.enabled</code> is true (the default), the package registers these named routes under the <code class="os-code-inline">cas</code> prefix — you do not declare them yourself:</p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>Auto-registered routes</span></div>
        <pre><code><span style="{{ $fn }}">GET</span>   /cas/login      <span style="{{ $com }}">// cas.login   → redirects to ONE</span>
<span style="{{ $fn }}">GET</span>   /cas/callback   <span style="{{ $com }}">// cas.callback → validates token, starts session</span>
<span style="{{ $fn }}">POST</span>  /cas/logout     <span style="{{ $com }}">// cas.logout</span>
<span style="{{ $fn }}">GET</span>   /cas/user       <span style="{{ $com }}">// cas.user    → current user as JSON</span></code></pre>
    </div>
</section>

{{-- 5. How the flow works --}}
<section id="flow" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">5. How the SSO flow works</h2>
    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-5">Authentication is browser-redirect plus a server-to-server token validation. The package handles every step below; this is what happens under the hood.</p>

    <ol class="space-y-4 mb-2">
        <li class="os-card os-card-pad flex gap-4">
            <span class="os-icon-tile os-icon-tile-ink shrink-0">1</span>
            <div>
                <p class="text-sm font-semibold text-[var(--color-ink)] mb-1">Redirect to ONE</p>
                <p class="text-sm text-[var(--color-muted)] leading-relaxed">A request to a <code class="os-code-inline">cas.auth</code> route with no session sends the browser to <code class="os-code-inline">GET {CAS_SERVER_URL}/sso/login?client_id=...</code>.</p>
            </div>
        </li>
        <li class="os-card os-card-pad flex gap-4">
            <span class="os-icon-tile os-icon-tile-ink shrink-0">2</span>
            <div>
                <p class="text-sm font-semibold text-[var(--color-ink)] mb-1">Callback with token</p>
                <p class="text-sm text-[var(--color-muted)] leading-relaxed">ONE authenticates the user and redirects back to your registered <code class="os-code-inline">callback_url</code> with the JWT appended as <code class="os-code-inline">?token=...</code>.</p>
            </div>
        </li>
        <li class="os-card os-card-pad flex gap-4">
            <span class="os-icon-tile os-icon-tile-ink shrink-0">3</span>
            <div>
                <p class="text-sm font-semibold text-[var(--color-ink)] mb-1">Server-to-server validation</p>
                <p class="text-sm text-[var(--color-muted)] leading-relaxed">The package posts <code class="os-code-inline">{ token, client_id, client_secret }</code> to <code class="os-code-inline">POST {CAS_SERVER_URL}/api/sso/validate</code>. On <code class="os-code-inline">200</code> it receives <code class="os-code-inline">{ valid, user, expires_at }</code>. The token is single-use.</p>
            </div>
        </li>
        <li class="os-card os-card-pad flex gap-4">
            <span class="os-icon-tile os-icon-tile-ink shrink-0">4</span>
            <div>
                <p class="text-sm font-semibold text-[var(--color-ink)] mb-1">Local session</p>
                <p class="text-sm text-[var(--color-muted)] leading-relaxed">The validated user is cached and stored in the session (<code class="os-code-inline">cas_user</code>, <code class="os-code-inline">cas_token</code>). With <code class="os-code-inline">create_local_users</code> on, a matching <code class="os-code-inline">App\Models\User</code> is found or created and logged in via Laravel <code class="os-code-inline">Auth</code>.</p>
            </div>
        </li>
    </ol>
</section>

{{-- 6. Code examples --}}
<section id="api" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">6. {{ $laravelGuide['sections']['examples'] }}</h2>

    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">Read the authenticated user from the session, or use the <code class="os-code-inline">CasClient</code> facade for role checks:</p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>app/Http/Controllers/DashboardController.php</span></div>
        <pre><code><span style="{{ $kw }}">use</span> <span style="{{ $fn }}">CasSystem\LaravelClient\Facades\CasClient</span>;

<span style="{{ $kw }}">public function</span> <span style="{{ $fn }}">index</span>(<span style="{{ $fn }}">Request</span> <span style="{{ $var }}">$request</span>)
{
    <span style="{{ $var }}">$user</span> = <span style="{{ $fn }}">session</span>(<span style="{{ $str }}">'cas_user'</span>); <span style="{{ $com }}">// ['id','username','email','name','roles' => [...]]</span>

    <span style="{{ $kw }}">if</span> (<span style="{{ $fn }}">CasClient</span>::<span style="{{ $fn }}">userHasRole</span>(<span style="{{ $var }}">$user</span>, <span style="{{ $str }}">'admin'</span>)) {
        <span style="{{ $com }}">// admin-only logic</span>
    }

    <span style="{{ $kw }}">return</span> <span style="{{ $fn }}">view</span>(<span style="{{ $str }}">'dashboard'</span>, <span style="{{ $fn }}">compact</span>(<span style="{{ $str }}">'user'</span>));
}</code></pre>
    </div>

    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">In Blade, gate UI on the CAS session and post to the named logout route:</p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>resources/views/dashboard.blade.php</span></div>
        <pre><code><span style="{{ $kw }}">&#64;if</span>(<span style="{{ $fn }}">session</span>(<span style="{{ $str }}">'authenticated'</span>))
    <span style="{{ $fn }}">&lt;p&gt;</span>Welcome, &#123;&#123; <span style="{{ $fn }}">session</span>(<span style="{{ $str }}">'cas_user.name'</span>) &#125;&#125;<span style="{{ $fn }}">&lt;/p&gt;</span>

    <span style="{{ $fn }}">&lt;form method=</span><span style="{{ $str }}">"POST"</span> <span style="{{ $fn }}">action=</span><span style="{{ $str }}">"&#123;&#123; route('cas.logout') &#125;&#125;"</span><span style="{{ $fn }}">&gt;</span>
        <span style="{{ $kw }}">&#64;csrf</span>
        <span style="{{ $fn }}">&lt;button type=</span><span style="{{ $str }}">"submit"</span><span style="{{ $fn }}">&gt;</span>Log out<span style="{{ $fn }}">&lt;/button&gt;</span>
    <span style="{{ $fn }}">&lt;/form&gt;</span>
<span style="{{ $kw }}">&#64;else</span>
    <span style="{{ $fn }}">&lt;a href=</span><span style="{{ $str }}">"&#123;&#123; route('cas.login') &#125;&#125;"</span><span style="{{ $fn }}">&gt;</span>Sign in with ONE<span style="{{ $fn }}">&lt;/a&gt;</span>
<span style="{{ $kw }}">&#64;endif</span></code></pre>
    </div>

    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">For service-to-service issuance, mint a token for a known user with <code class="os-code-inline">generateSSOToken()</code> (the call is IP-whitelisted on the ONE side):</p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>Service-to-service token</span></div>
        <pre><code><span style="{{ $kw }}">use</span> <span style="{{ $fn }}">CasSystem\LaravelClient\Facades\CasClient</span>;

<span style="{{ $com }}">// POST {CAS_SERVER_URL}/api/sso/token  { client_id, client_secret, username }</span>
<span style="{{ $var }}">$result</span> = <span style="{{ $fn }}">CasClient</span>::<span style="{{ $fn }}">generateSSOToken</span>(<span style="{{ $str }}">'jane.doe'</span>);

<span style="{{ $var }}">$token</span>       = <span style="{{ $var }}">$result</span>[<span style="{{ $str }}">'token'</span>] ?? <span style="{{ $kw }}">null</span>;
<span style="{{ $var }}">$redirectUrl</span> = <span style="{{ $var }}">$result</span>[<span style="{{ $str }}">'redirect_url'</span>] ?? <span style="{{ $kw }}">null</span>;</code></pre>
    </div>

    <p class="text-sm text-[var(--color-ink-2)] leading-relaxed mb-4">Facade methods available on <code class="os-code-inline">CasClient</code> (backed by <code class="os-code-inline">CasAuthService</code>):</p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>CasClient facade</span></div>
        <pre><code><span style="{{ $fn }}">CasClient</span>::<span style="{{ $fn }}">getLoginUrl</span>(<span style="{{ $var }}">$returnUrl</span>);              <span style="{{ $com }}">// string login URL</span>
<span style="{{ $fn }}">CasClient</span>::<span style="{{ $fn }}">validateToken</span>(<span style="{{ $var }}">$token</span>);              <span style="{{ $com }}">// ?array user, validates server-side</span>
<span style="{{ $fn }}">CasClient</span>::<span style="{{ $fn }}">getUserFromToken</span>(<span style="{{ $var }}">$token</span>);           <span style="{{ $com }}">// ?array cached user</span>
<span style="{{ $fn }}">CasClient</span>::<span style="{{ $fn }}">generateSSOToken</span>(<span style="{{ $var }}">$username</span>);        <span style="{{ $com }}">// ?array service-to-service</span>
<span style="{{ $fn }}">CasClient</span>::<span style="{{ $fn }}">logout</span>(<span style="{{ $var }}">$token</span>);                     <span style="{{ $com }}">// bool</span>
<span style="{{ $fn }}">CasClient</span>::<span style="{{ $fn }}">userHasRole</span>(<span style="{{ $var }}">$user</span>, <span style="{{ $str }}">'admin'</span>);
<span style="{{ $fn }}">CasClient</span>::<span style="{{ $fn }}">userHasAnyRole</span>(<span style="{{ $var }}">$user</span>, [<span style="{{ $str }}">'admin'</span>, <span style="{{ $str }}">'manager'</span>]);
<span style="{{ $fn }}">CasClient</span>::<span style="{{ $fn }}">userHasAllRoles</span>(<span style="{{ $var }}">$user</span>, [<span style="{{ $str }}">'user'</span>, <span style="{{ $str }}">'verified'</span>]);</code></pre>
    </div>

    <div class="os-alert os-alert-success mt-6">
        <i class="fas fa-check-circle mt-0.5"></i>
        <div>
            <strong class="font-semibold">Done.</strong> Your Laravel app now authenticates against ONE. Unauthenticated visitors to <code class="os-code-inline">cas.auth</code> routes are redirected to ONE and returned with an authenticated session. Serve over HTTPS in production and keep <code class="os-code-inline">CAS_CLIENT_SECRET</code> server-side only.
        </div>
    </div>
</section>

{{-- Next steps --}}
<section class="border-t border-[var(--color-line)] pt-10">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-4">Next steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="os-card os-card-hover group flex items-center gap-3 p-4">
            <i class="fas fa-code text-[var(--color-muted)] group-hover:text-[var(--color-accent)] text-sm"></i>
            <span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">API reference</span>
        </a>
        <a href="{{ route('docs.security') }}" class="os-card os-card-hover group flex items-center gap-3 p-4">
            <i class="fas fa-shield-alt text-[var(--color-muted)] group-hover:text-[var(--color-accent)] text-sm"></i>
            <span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">Security guide</span>
        </a>
        <a href="{{ route('docs.examples') }}" class="os-card os-card-hover group flex items-center gap-3 p-4">
            <i class="fas fa-book-open text-[var(--color-muted)] group-hover:text-[var(--color-accent)] text-sm"></i>
            <span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">Code examples</span>
        </a>
    </div>
</section>
@endsection
