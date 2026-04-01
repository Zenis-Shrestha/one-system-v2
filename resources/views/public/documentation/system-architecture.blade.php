@extends('public.documentation.layout')

@section('title', 'System Architecture — CAS SSO')
@section('description', 'Technical architecture overview of the CAS Single Sign-On authentication platform.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">Advanced Topics</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">System Architecture</h1>
        <p class="text-lg text-slate-500 leading-relaxed">Technical overview of the CAS SSO platform components and data flow.</p>
    </div>
</section>

<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-6">Architecture Overview</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mb-4"><i class="fas fa-server text-blue-600 text-sm"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1.5">CAS Server</h3>
            <p class="text-sm text-slate-500">Laravel-based authentication server. Handles user management, token generation, 2FA, and admin panel.</p>
            <div class="mt-3 flex flex-wrap gap-1">
                <span class="text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-600">Laravel 11</span>
                <span class="text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-600">PHP 8.2</span>
            </div>
        </div>
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center mb-4"><i class="fas fa-database text-emerald-600 text-sm"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1.5">Data Layer</h3>
            <p class="text-sm text-slate-500">PostgreSQL for persistent data. Redis for session management, caching, and rate limiting counters.</p>
            <div class="mt-3 flex flex-wrap gap-1">
                <span class="text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-600">PostgreSQL 16</span>
                <span class="text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-600">Redis 7</span>
            </div>
        </div>
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="w-10 h-10 bg-violet-50 rounded-lg flex items-center justify-center mb-4"><i class="fas fa-plug text-violet-600 text-sm"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1.5">Client SDKs</h3>
            <p class="text-sm text-slate-500">6 official client libraries. Each handles SSO token exchange and session management for its platform.</p>
            <div class="mt-3 flex flex-wrap gap-1">
                <span class="text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-600">Laravel</span>
                <span class="text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-600">Node.js</span>
                <span class="text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-600">Python</span>
            </div>
        </div>
    </div>
</section>

<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Request Flow</h2>
    <div class="space-y-0">
        <div class="flex gap-4">
            <div class="flex flex-col items-center"><div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">1</div><div class="w-px h-full bg-slate-200"></div></div>
            <div class="pb-6"><h3 class="text-sm font-semibold text-slate-900">Client Request</h3><p class="text-xs text-slate-500">Client app sends credentials + HMAC signature to CAS API</p></div>
        </div>
        <div class="flex gap-4">
            <div class="flex flex-col items-center"><div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">2</div><div class="w-px h-full bg-slate-200"></div></div>
            <div class="pb-6"><h3 class="text-sm font-semibold text-slate-900">Security Middleware</h3><p class="text-xs text-slate-500">IP whitelist check &rarr; Rate limit check &rarr; HMAC verification &rarr; reCAPTCHA validation</p></div>
        </div>
        <div class="flex gap-4">
            <div class="flex flex-col items-center"><div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">3</div><div class="w-px h-full bg-slate-200"></div></div>
            <div class="pb-6"><h3 class="text-sm font-semibold text-slate-900">Authentication</h3><p class="text-xs text-slate-500">Credential verification against PostgreSQL &rarr; 2FA challenge (if enabled) &rarr; Lockout check</p></div>
        </div>
        <div class="flex gap-4">
            <div class="flex flex-col items-center"><div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">4</div><div class="w-px h-full bg-slate-200"></div></div>
            <div class="pb-6"><h3 class="text-sm font-semibold text-slate-900">Token Generation</h3><p class="text-xs text-slate-500">JWT token created with HMAC-SHA256, stored in Redis, returned to client</p></div>
        </div>
        <div class="flex gap-4">
            <div class="flex flex-col items-center"><div class="w-8 h-8 bg-emerald-600 text-white rounded-full flex items-center justify-center text-xs font-bold">5</div></div>
            <div class="pb-4"><h3 class="text-sm font-semibold text-slate-900">Audit &amp; Webhooks</h3><p class="text-xs text-slate-500">Event logged to audit table, webhook dispatched to registered endpoints</p></div>
        </div>
    </div>
</section>

<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Database Schema</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Table</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Purpose</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Key Fields</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr>
                    <td class="px-5 py-3 font-mono text-xs text-slate-700">users</td>
                    <td class="px-5 py-3 text-slate-600">User accounts and profiles</td>
                    <td class="px-5 py-3 text-xs text-slate-500">email, password_hash, role, is_2fa_enabled</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-mono text-xs text-slate-700">client_systems</td>
                    <td class="px-5 py-3 text-slate-600">Registered applications</td>
                    <td class="px-5 py-3 text-xs text-slate-500">name, url, client_id, client_secret, ip_whitelist</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-mono text-xs text-slate-700">sso_tokens</td>
                    <td class="px-5 py-3 text-slate-600">Active JWT tokens</td>
                    <td class="px-5 py-3 text-xs text-slate-500">user_id, token_hash, expires_at, client_system_id</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-mono text-xs text-slate-700">login_attempts</td>
                    <td class="px-5 py-3 text-slate-600">Failed login tracking</td>
                    <td class="px-5 py-3 text-xs text-slate-500">email, ip, attempts, locked_until</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-mono text-xs text-slate-700">audit_logs</td>
                    <td class="px-5 py-3 text-slate-600">All auth events</td>
                    <td class="px-5 py-3 text-xs text-slate-500">user_id, action, ip, user_agent, timestamp</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

{{-- Technical Documentation — How the Code Functions --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Technical Documentation</h2>
    <p class="text-sm text-slate-600 mb-6">How the CAS codebase is structured and how each layer functions.</p>

    <div class="space-y-5">
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center"><i class="fas fa-folder-open text-blue-600 text-sm"></i></div>
                <h3 class="text-sm font-semibold text-slate-900">Directory Structure</h3>
            </div>
            <div class="rounded-lg bg-slate-900 p-4 overflow-x-auto">
                <pre class="text-xs font-mono text-slate-300 leading-relaxed"><code>app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          <span class="text-slate-500"># Admin dashboard, users, clients, audit logs</span>
│   │   ├── Auth/           <span class="text-slate-500"># Login, logout, 2FA verification</span>
│   │   ├── Api/            <span class="text-slate-500"># SSO token endpoints, health check</span>
│   │   └── Public/         <span class="text-slate-500"># Documentation, downloads</span>
│   ├── Middleware/
│   │   ├── AdminMiddleware.php        <span class="text-slate-500"># Role-based admin access</span>
│   │   ├── IpWhitelistMiddleware.php  <span class="text-slate-500"># IP filtering (fail-open)</span>
│   │   └── HmacMiddleware.php         <span class="text-slate-500"># HMAC signature verification</span>
│   └── Livewire/           <span class="text-slate-500"># Real-time admin components</span>
├── Models/
│   ├── User.php            <span class="text-slate-500"># User accounts with role & 2FA</span>
│   ├── ClientSystem.php    <span class="text-slate-500"># Registered applications</span>
│   ├── SsoToken.php        <span class="text-slate-500"># JWT token records</span>
│   ├── AuditLog.php        <span class="text-slate-500"># Authentication event log</span>
│   └── IpWhitelist.php     <span class="text-slate-500"># Allowed IP addresses</span>
└── Services/
    └── SsoService.php      <span class="text-slate-500"># Core SSO logic (token gen/validation)</span></code></pre>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="p-5 rounded-xl border border-slate-200">
                <h3 class="text-sm font-semibold text-slate-900 mb-3"><i class="fas fa-cogs text-emerald-500 mr-2"></i>Middleware Pipeline</h3>
                <p class="text-xs text-slate-500 mb-3">Every web request passes through these layers (in order):</p>
                <ol class="space-y-2 text-xs text-slate-600">
                    <li class="flex items-start gap-2"><span class="font-bold text-slate-900">1.</span> IP Whitelist Check — blocks unregistered IPs (if whitelist is populated)</li>
                    <li class="flex items-start gap-2"><span class="font-bold text-slate-900">2.</span> Session/Auth — verifies the user's session</li>
                    <li class="flex items-start gap-2"><span class="font-bold text-slate-900">3.</span> CSRF Protection — validates form tokens</li>
                    <li class="flex items-start gap-2"><span class="font-bold text-slate-900">4.</span> Admin Middleware — restricts admin routes to role=admin</li>
                </ol>
                <p class="text-xs text-slate-500 mt-3">API routes use HMAC middleware instead of CSRF/session.</p>
            </div>
            <div class="p-5 rounded-xl border border-slate-200">
                <h3 class="text-sm font-semibold text-slate-900 mb-3"><i class="fas fa-bolt text-violet-500 mr-2"></i>Livewire Components</h3>
                <p class="text-xs text-slate-500 mb-3">The admin panel uses Livewire for real-time updates:</p>
                <ul class="space-y-2 text-xs text-slate-600">
                    <li class="flex items-start gap-2"><i class="fas fa-check text-emerald-500 mt-0.5"></i> <strong>Dashboard Stats</strong> — live counters for users, tokens, clients</li>
                    <li class="flex items-start gap-2"><i class="fas fa-check text-emerald-500 mt-0.5"></i> <strong>User Management</strong> — inline create/edit/delete with instant feedback</li>
                    <li class="flex items-start gap-2"><i class="fas fa-check text-emerald-500 mt-0.5"></i> <strong>Client Systems</strong> — credential generation and copy-to-clipboard</li>
                    <li class="flex items-start gap-2"><i class="fas fa-check text-emerald-500 mt-0.5"></i> <strong>Audit Log Viewer</strong> — paginated, filterable event log</li>
                    <li class="flex items-start gap-2"><i class="fas fa-check text-emerald-500 mt-0.5"></i> <strong>IP Whitelist</strong> — add/remove with instant validation</li>
                </ul>
            </div>
        </div>

        <div class="p-5 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-3"><i class="fas fa-database text-blue-500 mr-2"></i>Models & Relationships</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-600">
                <div>
                    <p class="font-semibold text-slate-900 mb-1">User</p>
                    <ul class="space-y-1 ml-3">
                        <li>→ hasMany <code class="bg-slate-100 px-1 rounded">SsoToken</code></li>
                        <li>→ hasMany <code class="bg-slate-100 px-1 rounded">AuditLog</code></li>
                        <li>→ belongsToMany <code class="bg-slate-100 px-1 rounded">ClientSystem</code></li>
                    </ul>
                </div>
                <div>
                    <p class="font-semibold text-slate-900 mb-1">ClientSystem</p>
                    <ul class="space-y-1 ml-3">
                        <li>→ hasMany <code class="bg-slate-100 px-1 rounded">SsoToken</code></li>
                        <li>→ belongsToMany <code class="bg-slate-100 px-1 rounded">User</code></li>
                        <li>→ hasMany <code class="bg-slate-100 px-1 rounded">IpWhitelist</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Package Documentation — How Each SDK Functions --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Package Documentation</h2>
    <p class="text-sm text-slate-600 mb-6">How each client SDK package works internally to integrate with CAS.</p>

    <div class="p-5 rounded-xl border border-slate-200 mb-5">
        <h3 class="text-sm font-semibold text-slate-900 mb-3"><i class="fas fa-exchange-alt text-blue-500 mr-2"></i>Common SDK Flow</h3>
        <p class="text-xs text-slate-500 mb-4">All SDK packages follow the same core authentication pattern:</p>
        <div class="space-y-0">
            <div class="flex gap-4">
                <div class="flex flex-col items-center"><div class="w-7 h-7 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">1</div><div class="w-px h-full bg-slate-200"></div></div>
                <div class="pb-4"><h4 class="text-xs font-semibold text-slate-900">User Visits Protected Route</h4><p class="text-xs text-slate-500">SDK middleware intercepts the request and checks for a valid CAS session</p></div>
            </div>
            <div class="flex gap-4">
                <div class="flex flex-col items-center"><div class="w-7 h-7 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">2</div><div class="w-px h-full bg-slate-200"></div></div>
                <div class="pb-4"><h4 class="text-xs font-semibold text-slate-900">Redirect to CAS Login</h4><p class="text-xs text-slate-500">If no session exists, user is redirected to CAS login page with a return URL</p></div>
            </div>
            <div class="flex gap-4">
                <div class="flex flex-col items-center"><div class="w-7 h-7 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">3</div><div class="w-px h-full bg-slate-200"></div></div>
                <div class="pb-4"><h4 class="text-xs font-semibold text-slate-900">CAS Authenticates & Generates Token</h4><p class="text-xs text-slate-500">After successful login, CAS generates a JWT token and redirects back with the token</p></div>
            </div>
            <div class="flex gap-4">
                <div class="flex flex-col items-center"><div class="w-7 h-7 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">4</div><div class="w-px h-full bg-slate-200"></div></div>
                <div class="pb-4"><h4 class="text-xs font-semibold text-slate-900">SDK Validates Token via API</h4><p class="text-xs text-slate-500">The SDK sends the token to the CAS <code class="bg-slate-100 px-1 rounded text-xs">/api/sso/validate</code> endpoint with HMAC signature</p></div>
            </div>
            <div class="flex gap-4">
                <div class="flex flex-col items-center"><div class="w-7 h-7 bg-emerald-600 text-white rounded-full flex items-center justify-center text-xs font-bold">5</div></div>
                <div class="pb-2"><h4 class="text-xs font-semibold text-slate-900">Session Created</h4><p class="text-xs text-slate-500">SDK stores user data in the local session. User is now authenticated.</p></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('docs.laravel') }}" class="group p-4 rounded-xl border border-slate-200 hover:border-red-200 hover:bg-red-50/30 transition-all">
            <div class="flex items-center gap-2 mb-2"><i class="fab fa-laravel text-red-500"></i><span class="text-sm font-semibold text-slate-900">Laravel</span></div>
            <p class="text-xs text-slate-500">Composer package with auto-discovery. Provides <code class="bg-slate-100 px-1 rounded">cas.auth</code> middleware, service provider, and config publishing.</p>
        </a>
        <a href="{{ route('docs.nodejs') }}" class="group p-4 rounded-xl border border-slate-200 hover:border-green-200 hover:bg-green-50/30 transition-all">
            <div class="flex items-center gap-2 mb-2"><i class="fab fa-node-js text-green-500"></i><span class="text-sm font-semibold text-slate-900">Node.js</span></div>
            <p class="text-xs text-slate-500">Express middleware via npm. Provides <code class="bg-slate-100 px-1 rounded">casAuth()</code> middleware and <code class="bg-slate-100 px-1 rounded">CasClient</code> class.</p>
        </a>
        <a href="{{ route('docs.python') }}" class="group p-4 rounded-xl border border-slate-200 hover:border-indigo-200 hover:bg-indigo-50/30 transition-all">
            <div class="flex items-center gap-2 mb-2"><i class="fab fa-python text-indigo-500"></i><span class="text-sm font-semibold text-slate-900">Python</span></div>
            <p class="text-xs text-slate-500">Django middleware via pip. Provides <code class="bg-slate-100 px-1 rounded">CasAuthMiddleware</code> class and management commands.</p>
        </a>
        <a href="{{ route('docs.java') }}" class="group p-4 rounded-xl border border-slate-200 hover:border-orange-200 hover:bg-orange-50/30 transition-all">
            <div class="flex items-center gap-2 mb-2"><i class="fab fa-java text-orange-500"></i><span class="text-sm font-semibold text-slate-900">Java</span></div>
            <p class="text-xs text-slate-500">Spring Security filter via Maven/Gradle. <code class="bg-slate-100 px-1 rounded">CasAuthFilter</code> integrates with Spring's filter chain.</p>
        </a>
        <a href="{{ route('docs.dotnet') }}" class="group p-4 rounded-xl border border-slate-200 hover:border-blue-200 hover:bg-blue-50/30 transition-all">
            <div class="flex items-center gap-2 mb-2"><i class="fab fa-microsoft text-blue-500"></i><span class="text-sm font-semibold text-slate-900">.NET / C#</span></div>
            <p class="text-xs text-slate-500">NuGet package with <code class="bg-slate-100 px-1 rounded">CasAuthFilter</code> action filter and DI-based configuration.</p>
        </a>
        <a href="{{ route('docs.javascript') }}" class="group p-4 rounded-xl border border-slate-200 hover:border-yellow-200 hover:bg-yellow-50/30 transition-all">
            <div class="flex items-center gap-2 mb-2"><i class="fab fa-js text-yellow-500"></i><span class="text-sm font-semibold text-slate-900">JavaScript</span></div>
            <p class="text-xs text-slate-500">Browser SDK via CDN or npm. Provides <code class="bg-slate-100 px-1 rounded">CasClient</code> class for SPAs and static sites.</p>
        </a>
    </div>
</section>

{{-- API Documentation — How the API Functions --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">API Documentation</h2>
    <p class="text-sm text-slate-600 mb-6">Summary of the CAS API endpoints. <a href="{{ route('docs.api.overview') }}" class="text-blue-600 font-medium hover:text-blue-800">View full API Reference →</a></p>

    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Method</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Endpoint</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Purpose</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Auth</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr>
                    <td class="px-5 py-3"><span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">POST</span></td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/sso/token</td>
                    <td class="px-5 py-3 text-slate-600">Generate SSO token (authenticate user)</td>
                    <td class="px-5 py-3 text-xs text-slate-500">HMAC + Client credentials</td>
                </tr>
                <tr>
                    <td class="px-5 py-3"><span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">POST</span></td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/sso/validate</td>
                    <td class="px-5 py-3 text-slate-600">Validate an existing token</td>
                    <td class="px-5 py-3 text-xs text-slate-500">HMAC</td>
                </tr>
                <tr>
                    <td class="px-5 py-3"><span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">POST</span></td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/sso/logout</td>
                    <td class="px-5 py-3 text-slate-600">Revoke/invalidate a token</td>
                    <td class="px-5 py-3 text-xs text-slate-500">HMAC</td>
                </tr>
                <tr>
                    <td class="px-5 py-3"><span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">GET</span></td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/health</td>
                    <td class="px-5 py-3 text-slate-600">Health check (server status)</td>
                    <td class="px-5 py-3 text-xs text-slate-500">None</td>
                </tr>
                <tr>
                    <td class="px-5 py-3"><span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">GET</span></td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/sso/user</td>
                    <td class="px-5 py-3 text-slate-600">Get authenticated user details</td>
                    <td class="px-5 py-3 text-xs text-slate-500">Bearer token</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
            <span class="text-sm text-blue-800">All API requests (except health check) require HMAC-SHA256 signatures in the <code class="bg-blue-100 px-1 py-0.5 rounded font-mono text-xs">X-Signature</code> and <code class="bg-blue-100 px-1 py-0.5 rounded font-mono text-xs">X-Timestamp</code> headers. See <a href="{{ route('docs.security') }}" class="font-semibold underline">Security Features</a> for details.</span>
        </div>
    </div>
</section>

<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Technology Stack</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl border border-slate-200 text-center">
            <i class="fab fa-laravel text-red-500 text-2xl mb-2"></i>
            <p class="text-xs font-semibold text-slate-900">Laravel 11</p>
            <p class="text-xs text-slate-400">Backend</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200 text-center">
            <i class="fas fa-database text-blue-500 text-2xl mb-2"></i>
            <p class="text-xs font-semibold text-slate-900">PostgreSQL</p>
            <p class="text-xs text-slate-400">Database</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200 text-center">
            <i class="fas fa-bolt text-red-500 text-2xl mb-2"></i>
            <p class="text-xs font-semibold text-slate-900">Redis</p>
            <p class="text-xs text-slate-400">Cache / Sessions</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200 text-center">
            <i class="fab fa-docker text-blue-500 text-2xl mb-2"></i>
            <p class="text-xs font-semibold text-slate-900">Docker</p>
            <p class="text-xs text-slate-400">Deployment</p>
        </div>
    </div>
</section>
@endsection