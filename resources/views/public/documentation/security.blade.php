@extends('public.documentation.layout')

@section('title', 'Security Features — CAS SSO')
@section('description', 'Enterprise-grade security features including 2FA, HMAC signatures, rate limiting, and audit logging.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">Getting Started</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">Security Features</h1>
        <p class="text-lg text-slate-500 leading-relaxed">Multiple layers of defense protecting your authentication infrastructure.</p>
    </div>
</section>

{{-- Security Layers --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-6">Defense Layers</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mb-4"><i class="fas fa-key text-blue-600 text-sm"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1.5">HMAC-SHA256 Signatures</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Every API request is signed with HMAC-SHA256 using your client secret. The server verifies the signature before processing, preventing request tampering and replay attacks.</p>
        </div>
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center mb-4"><i class="fas fa-mobile-alt text-emerald-600 text-sm"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1.5">Two-Factor Authentication</h3>
            <p class="text-sm text-slate-500 leading-relaxed">TOTP-based 2FA with QR code setup. Backup recovery codes provided during enrollment. Compatible with Google Authenticator and Authy.</p>
        </div>
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center mb-4"><i class="fas fa-tachometer-alt text-amber-600 text-sm"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1.5">Rate Limiting</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Granular per-endpoint throttling: 10 req/min for auth, 100 req/min for validation, 50 req/min for user management. Returns <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">429</code> with retry header.</p>
        </div>
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center mb-4"><i class="fas fa-ban text-red-600 text-sm"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1.5">Account Lockout</h3>
            <p class="text-sm text-slate-500 leading-relaxed">After 5 consecutive failed login attempts, the account is locked for 30 minutes. Admins can manually unlock accounts from the dashboard.</p>
        </div>
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="w-10 h-10 bg-violet-50 rounded-lg flex items-center justify-center mb-4"><i class="fas fa-robot text-violet-600 text-sm"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1.5">reCAPTCHA v3</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Google reCAPTCHA v3 integration on login forms prevents automated credential-stuffing attacks without user friction.</p>
        </div>
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center mb-4"><i class="fas fa-network-wired text-slate-600 text-sm"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1.5">IP Whitelisting</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Client application IPs must be pre-registered. Requests from unregistered IPs are rejected before reaching the authentication layer.</p>
        </div>
    </div>
</section>

{{-- JWT Structure --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">JWT Token Structure</h2>
    <p class="text-sm text-slate-600 mb-4">Tokens are signed with HMAC-SHA256 and contain the following claims:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "<span class="text-amber-300">sub</span>": <span class="text-blue-400">42</span>,
  "<span class="text-amber-300">email</span>": "<span class="text-green-400">john@example.com</span>",
  "<span class="text-amber-300">role</span>": "<span class="text-green-400">user</span>",
  "<span class="text-amber-300">client_id</span>": "<span class="text-green-400">customer-portal</span>",
  "<span class="text-amber-300">iat</span>": <span class="text-blue-400">1710072000</span>,
  "<span class="text-amber-300">exp</span>": <span class="text-blue-400">1710075600</span>,
  "<span class="text-amber-300">security</span>": {
    "<span class="text-amber-300">2fa_verified</span>": <span class="text-blue-400">true</span>,
    "<span class="text-amber-300">ip</span>": "<span class="text-green-400">192.168.1.10</span>"
  }
}</code></pre>
        </div>
    </div>
</section>

{{-- HMAC Example --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">HMAC Signature Generation</h2>
    <p class="text-sm text-slate-600 mb-4">Calculate the signature using your client secret and the request body:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">PHP example</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-red-300">$body</span>      = <span class="text-green-400">json_encode</span>(<span class="text-red-300">$requestData</span>);
<span class="text-red-300">$timestamp</span> = <span class="text-green-400">time</span>();
<span class="text-red-300">$payload</span>   = <span class="text-red-300">$timestamp</span> . <span class="text-amber-300">'.'</span> . <span class="text-red-300">$body</span>;
<span class="text-red-300">$signature</span> = <span class="text-green-400">hash_hmac</span>(<span class="text-amber-300">'sha256'</span>, <span class="text-red-300">$payload</span>, <span class="text-red-300">$clientSecret</span>);

<span class="text-slate-500">// Send with headers:</span>
<span class="text-slate-500">// X-Signature: sha256=$signature</span>
<span class="text-slate-500">// X-Timestamp: $timestamp</span></code></pre>
        </div>
    </div>
</section>

{{-- Audit Logging --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Audit Logging</h2>
    <p class="text-sm text-slate-600 mb-4">Every authentication event is logged with full context:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Field</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr><td class="px-5 py-3 font-mono text-xs text-slate-700">user_id</td><td class="px-5 py-3 text-slate-600">Authenticated user identifier</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs text-slate-700">ip_address</td><td class="px-5 py-3 text-slate-600">Client IP address</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs text-slate-700">user_agent</td><td class="px-5 py-3 text-slate-600">Browser / device identifier</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs text-slate-700">action</td><td class="px-5 py-3 text-slate-600">login, logout, failed_login, lockout</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs text-slate-700">client_system</td><td class="px-5 py-3 text-slate-600">Originating application</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs text-slate-700">timestamp</td><td class="px-5 py-3 text-slate-600">ISO 8601 event timestamp</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs text-slate-700">geo_location</td><td class="px-5 py-3 text-slate-600">Approximate location from IP</td></tr>
            </tbody>
        </table>
    </div>
</section>

{{-- Best Practices --}}
<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Security Best Practices</h2>
    <div class="space-y-3">
        <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="text-sm text-slate-600"><strong class="text-slate-900">Enforce HTTPS</strong> — never transmit tokens over plain HTTP in production.</div>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="text-sm text-slate-600"><strong class="text-slate-900">Rotate secrets</strong> — change client secrets every 90 days minimum.</div>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="text-sm text-slate-600"><strong class="text-slate-900">Enable 2FA</strong> — require two-factor authentication for all admin accounts.</div>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="text-sm text-slate-600"><strong class="text-slate-900">Monitor audit logs</strong> — set up alerts for unusual login patterns or lockout events.</div>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="text-sm text-slate-600"><strong class="text-slate-900">Validate signatures</strong> — always verify the HMAC signature on webhook payloads.</div>
        </div>
    </div>
</section>
@endsection