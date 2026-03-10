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