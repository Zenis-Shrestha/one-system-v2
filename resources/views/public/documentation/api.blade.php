@extends('public.documentation.layout')

@section('title', 'API Reference — CAS SSO')
@section('description', 'Complete REST API reference for CAS Single Sign-On authentication system endpoints and responses.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">Reference</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">API Reference</h1>
        <p class="text-lg text-slate-500 leading-relaxed">Complete REST API documentation for the CAS authentication system.</p>
        <div class="flex flex-wrap gap-3 mt-6">
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-700"><i class="fas fa-network-wired mr-1.5"></i>IP Whitelisted</span>
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-200 text-xs font-medium text-blue-700"><i class="fas fa-ban mr-1.5"></i>Account Lockout</span>
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-violet-50 border border-violet-200 text-xs font-medium text-violet-700"><i class="fas fa-tachometer-alt mr-1.5"></i>Rate Limited</span>
        </div>
    </div>
</section>

{{-- Base URL --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Base URL &amp; Security</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>https://your-cas-server.com/api</code></pre>
        </div>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
            <div class="text-sm text-amber-800">
                <strong>Security Requirements</strong>
                <ul class="mt-2 space-y-1 text-amber-700">
                    <li>IP Whitelisting — client system IPs must be registered in the admin panel</li>
                    <li>Rate Limiting — login endpoints are rate limited per IP address</li>
                    <li>Account lockout after 5 failed login attempts (30 min cooldown)</li>
                    <li>All SSO endpoints require valid <code class="text-xs bg-amber-100 px-1 py-0.5 rounded font-mono">client_id</code> and <code class="text-xs bg-amber-100 px-1 py-0.5 rounded font-mono">client_secret</code></li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- POST /api/sso/token --}}
<section class="mb-12" id="sso-token">
    <div class="flex items-center gap-3 mb-4">
        <span class="inline-flex items-center px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded">POST</span>
        <code class="text-base font-mono font-semibold text-slate-900">/api/sso/token</code>
    </div>
    <p class="text-sm text-slate-500 mb-6">Generate an SSO token for a user. This is a server-to-server call using client credentials and a username. The endpoint is protected by IP whitelisting.</p>

    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Request Body</h3>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "<span class="text-amber-300">client_id</span>": "<span class="text-green-400">your_client_id</span>",
  "<span class="text-amber-300">client_secret</span>": "<span class="text-green-400">your_client_secret</span>",
  "<span class="text-amber-300">username</span>": "<span class="text-green-400">john_doe</span>"
}</code></pre>
        </div>
    </div>

    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Success Response <span class="text-emerald-600">200</span></h3>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "<span class="text-amber-300">redirect_url</span>": "<span class="text-green-400">https://your-app.com/cas/callback?token=eyJhbG...</span>",
  "<span class="text-amber-300">token</span>": "<span class="text-green-400">eyJhbGciOiJIUzI1NiIs...</span>"
}</code></pre>
        </div>
    </div>

    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Error Responses</h3>
    <div class="rounded-xl border border-red-200 overflow-hidden">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-slate-500">// 401 — Invalid client credentials</span>
{ "<span class="text-amber-300">error</span>": "<span class="text-red-400">Invalid client credentials</span>" }

<span class="text-slate-500">// 404 — User not found</span>
{ "<span class="text-amber-300">error</span>": "<span class="text-red-400">User not found or inactive</span>" }</code></pre>
        </div>
    </div>
</section>

{{-- POST /api/validate-token --}}
<section class="mb-12" id="sso-validate">
    <div class="flex items-center gap-3 mb-4">
        <span class="inline-flex items-center px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded">POST</span>
        <code class="text-base font-mono font-semibold text-slate-900">/api/validate-token</code>
    </div>
    <p class="text-sm text-slate-500 mb-6">Validate an SSO token and retrieve user information. Tokens are single-use — once validated, they cannot be reused. This endpoint is also protected by IP whitelisting.</p>

    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Request Body</h3>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "<span class="text-amber-300">token</span>": "<span class="text-green-400">eyJhbGciOiJIUzI1NiIs...</span>",
  "<span class="text-amber-300">client_id</span>": "<span class="text-green-400">your_client_id</span>",
  "<span class="text-amber-300">client_secret</span>": "<span class="text-green-400">your_client_secret</span>"
}</code></pre>
        </div>
    </div>

    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Response <span class="text-emerald-600">200</span></h3>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "<span class="text-amber-300">valid</span>": <span class="text-blue-400">true</span>,
  "<span class="text-amber-300">user</span>": {
    "<span class="text-amber-300">id</span>": <span class="text-blue-400">1</span>,
    "<span class="text-amber-300">username</span>": "<span class="text-green-400">john_doe</span>",
    "<span class="text-amber-300">email</span>": "<span class="text-green-400">john@example.com</span>"
  },
  "<span class="text-amber-300">expires_at</span>": "<span class="text-green-400">2026-03-10 22:30:00</span>"
}</code></pre>
        </div>
    </div>
</section>

{{-- Web SSO Flow --}}
<section class="mb-12" id="web-sso">
    <div class="flex items-center gap-3 mb-4">
        <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded">GET</span>
        <code class="text-base font-mono font-semibold text-slate-900">/sso/login</code>
    </div>
    <p class="text-sm text-slate-500 mb-6">Initiate browser-based SSO login. Redirect users to this URL to authenticate via the CAS login page. After login, users are redirected to your callback URL with a token.</p>

    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Query Parameters</h3>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Parameter</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr><td class="px-5 py-3 font-mono text-sm text-blue-600">client_id</td><td class="px-5 py-3 text-slate-600">Your registered client system ID</td></tr>
            </tbody>
        </table>
    </div>

    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Callback</h3>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-slate-500">// After successful login, user is redirected to:</span>
https://your-app.com/cas/callback?token=eyJhbGciOiJIUzI1NiIs...

<span class="text-slate-500">// Your callback should validate the token via POST /api/validate-token</span></code></pre>
        </div>
    </div>
</section>

{{-- GET /api/user --}}
<section class="mb-12" id="get-user">
    <div class="flex items-center gap-3 mb-4">
        <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded">GET</span>
        <code class="text-base font-mono font-semibold text-slate-900">/api/user</code>
    </div>
    <p class="text-sm text-slate-500 mb-6">Retrieve the currently authenticated user's profile (session-based).</p>

    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Response <span class="text-emerald-600">200</span></h3>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "<span class="text-amber-300">id</span>": <span class="text-blue-400">1</span>,
  "<span class="text-amber-300">username</span>": "<span class="text-green-400">john_doe</span>",
  "<span class="text-amber-300">email</span>": "<span class="text-green-400">john@example.com</span>",
  "<span class="text-amber-300">role</span>": "<span class="text-green-400">user</span>",
  "<span class="text-amber-300">full_name</span>": "<span class="text-green-400">John Doe</span>"
}</code></pre>
        </div>
    </div>
</section>

{{-- POST /api/auth/login --}}
<section class="mb-12" id="auth-login">
    <div class="flex items-center gap-3 mb-4">
        <span class="inline-flex items-center px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded">POST</span>
        <code class="text-base font-mono font-semibold text-slate-900">/api/auth/login</code>
    </div>
    <p class="text-sm text-slate-500 mb-6">Authenticate a user via the API. Returns user data on success.</p>

    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Request Body</h3>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "<span class="text-amber-300">login</span>": "<span class="text-green-400">john_doe</span>",
  "<span class="text-amber-300">password</span>": "<span class="text-green-400">your_password</span>"
}</code></pre>
        </div>
    </div>

    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Success Response <span class="text-emerald-600">200</span></h3>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "<span class="text-amber-300">success</span>": <span class="text-blue-400">true</span>,
  "<span class="text-amber-300">user</span>": {
    "<span class="text-amber-300">id</span>": <span class="text-blue-400">1</span>,
    "<span class="text-amber-300">username</span>": "<span class="text-green-400">john_doe</span>",
    "<span class="text-amber-300">email</span>": "<span class="text-green-400">john@example.com</span>",
    "<span class="text-amber-300">role</span>": "<span class="text-green-400">user</span>",
    "<span class="text-amber-300">full_name</span>": "<span class="text-green-400">John Doe</span>"
  }
}</code></pre>
        </div>
    </div>

    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Error Responses</h3>
    <div class="rounded-xl border border-red-200 overflow-hidden">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-slate-500">// 401 — Invalid credentials</span>
{ "<span class="text-amber-300">error</span>": "<span class="text-red-400">Invalid credentials</span>" }

<span class="text-slate-500">// 423 — Account locked (too many failed attempts)</span>
{ "<span class="text-amber-300">error</span>": "<span class="text-red-400">Account locked</span>", "<span class="text-amber-300">remaining_minutes</span>": <span class="text-blue-400">25</span> }

<span class="text-slate-500">// 429 — Rate limited</span>
{ "<span class="text-amber-300">error</span>": "<span class="text-red-400">Too many attempts</span>", "<span class="text-amber-300">retry_after</span>": <span class="text-blue-400">45</span> }</code></pre>
        </div>
    </div>
</section>

{{-- Status Codes --}}
<section class="mb-12" id="status-codes">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">HTTP Status Codes</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Code</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Meaning</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr><td class="px-5 py-3 font-mono text-emerald-600">200</td><td class="px-5 py-3 text-slate-600">Success</td></tr>
                <tr><td class="px-5 py-3 font-mono text-emerald-600">201</td><td class="px-5 py-3 text-slate-600">Created</td></tr>
                <tr><td class="px-5 py-3 font-mono text-amber-600">400</td><td class="px-5 py-3 text-slate-600">Bad Request — validation error</td></tr>
                <tr><td class="px-5 py-3 font-mono text-red-600">401</td><td class="px-5 py-3 text-slate-600">Unauthorized — invalid credentials or token</td></tr>
                <tr><td class="px-5 py-3 font-mono text-red-600">403</td><td class="px-5 py-3 text-slate-600">Forbidden — IP not whitelisted</td></tr>
                <tr><td class="px-5 py-3 font-mono text-red-600">404</td><td class="px-5 py-3 text-slate-600">Not Found — user or resource not found</td></tr>
                <tr><td class="px-5 py-3 font-mono text-red-600">423</td><td class="px-5 py-3 text-slate-600">Locked — account lockout active</td></tr>
                <tr><td class="px-5 py-3 font-mono text-red-600">429</td><td class="px-5 py-3 text-slate-600">Too Many Requests — rate limited</td></tr>
                <tr><td class="px-5 py-3 font-mono text-red-600">500</td><td class="px-5 py-3 text-slate-600">Internal Server Error</td></tr>
            </tbody>
        </table>
    </div>
</section>

{{-- Rate Limiting --}}
<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Rate Limits</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Authentication</h3>
            <p class="text-2xl font-bold text-slate-900">10<span class="text-sm font-normal text-slate-400"> /min</span></p>
            <p class="text-xs text-slate-500 mt-1">Per IP address</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Token Validation</h3>
            <p class="text-2xl font-bold text-slate-900">100<span class="text-sm font-normal text-slate-400"> /min</span></p>
            <p class="text-xs text-slate-500 mt-1">Per client system</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-1">User Management</h3>
            <p class="text-2xl font-bold text-slate-900">50<span class="text-sm font-normal text-slate-400"> /min</span></p>
            <p class="text-xs text-slate-500 mt-1">Per authenticated user</p>
        </div>
    </div>
</section>
@endsection
