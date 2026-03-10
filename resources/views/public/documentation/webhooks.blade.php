@extends('public.documentation.layout')

@section('title', 'Webhooks — CAS SSO')
@section('description', 'Configure webhook endpoints to receive real-time notifications for authentication events.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">Technical Reference</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">Webhooks</h1>
        <p class="text-lg text-slate-500 leading-relaxed">Receive real-time HTTP callbacks when authentication events occur in the CAS system.</p>
    </div>
</section>

{{-- Overview --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">How It Works</h2>
    <p class="text-sm text-slate-600 leading-relaxed mb-6">When an event fires — such as a user login, logout, or failed authentication attempt — CAS sends a <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono">POST</code> request to your registered webhook URL with a JSON payload describing the event. All payloads include an HMAC-SHA256 signature in the <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono">X-CAS-Signature</code> header for verification.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="p-4 rounded-xl border border-slate-200">
            <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-bolt text-blue-600 text-sm"></i>
            </div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Real-Time</h3>
            <p class="text-xs text-slate-500">Events fire within milliseconds of the triggering action.</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <div class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-redo text-emerald-600 text-sm"></i>
            </div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Auto Retry</h3>
            <p class="text-xs text-slate-500">Failed deliveries are retried 3 times with exponential backoff.</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <div class="w-9 h-9 bg-violet-50 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-shield-alt text-violet-600 text-sm"></i>
            </div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Signed</h3>
            <p class="text-xs text-slate-500">HMAC-SHA256 signatures prevent spoofing and tampering.</p>
        </div>
    </div>
</section>

{{-- Event Types --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Event Types</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Event</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Description</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Trigger</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr>
                    <td class="px-5 py-3"><code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono">user.login</code></td>
                    <td class="px-5 py-3 text-slate-600">Successful authentication</td>
                    <td class="px-5 py-3 text-slate-500">SSO token issued</td>
                </tr>
                <tr>
                    <td class="px-5 py-3"><code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono">user.logout</code></td>
                    <td class="px-5 py-3 text-slate-600">User session ended</td>
                    <td class="px-5 py-3 text-slate-500">Token invalidated</td>
                </tr>
                <tr>
                    <td class="px-5 py-3"><code class="text-xs bg-red-50 px-1.5 py-0.5 rounded font-mono text-red-700">user.login_failed</code></td>
                    <td class="px-5 py-3 text-slate-600">Failed login attempt</td>
                    <td class="px-5 py-3 text-slate-500">Invalid credentials</td>
                </tr>
                <tr>
                    <td class="px-5 py-3"><code class="text-xs bg-red-50 px-1.5 py-0.5 rounded font-mono text-red-700">user.locked</code></td>
                    <td class="px-5 py-3 text-slate-600">Account locked out</td>
                    <td class="px-5 py-3 text-slate-500">5 failed attempts</td>
                </tr>
                <tr>
                    <td class="px-5 py-3"><code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono">user.2fa_enabled</code></td>
                    <td class="px-5 py-3 text-slate-600">2FA activated</td>
                    <td class="px-5 py-3 text-slate-500">User enabled TOTP</td>
                </tr>
                <tr>
                    <td class="px-5 py-3"><code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono">token.expired</code></td>
                    <td class="px-5 py-3 text-slate-600">Token reached expiry</td>
                    <td class="px-5 py-3 text-slate-500">JWT TTL elapsed</td>
                </tr>
                <tr>
                    <td class="px-5 py-3"><code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono">client.registered</code></td>
                    <td class="px-5 py-3 text-slate-600">New client system added</td>
                    <td class="px-5 py-3 text-slate-500">Admin action</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

{{-- Payload Example --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Payload Format</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">user.login event payload</span>
            <span class="text-xs text-slate-400">application/json</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "<span class="text-amber-300">event</span>": "<span class="text-green-400">user.login</span>",
  "<span class="text-amber-300">timestamp</span>": "<span class="text-green-400">2026-03-10T12:00:00Z</span>",
  "<span class="text-amber-300">data</span>": {
    "<span class="text-amber-300">user_id</span>": <span class="text-blue-400">42</span>,
    "<span class="text-amber-300">email</span>": "<span class="text-green-400">john@example.com</span>",
    "<span class="text-amber-300">ip_address</span>": "<span class="text-green-400">192.168.1.10</span>",
    "<span class="text-amber-300">user_agent</span>": "<span class="text-green-400">Mozilla/5.0 ...</span>",
    "<span class="text-amber-300">client_system</span>": "<span class="text-green-400">customer-portal</span>",
    "<span class="text-amber-300">2fa_used</span>": <span class="text-blue-400">true</span>
  }
}</code></pre>
        </div>
    </div>
</section>

{{-- Signature Verification --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Signature Verification</h2>
    <p class="text-sm text-slate-600 leading-relaxed mb-4">Every webhook request contains an <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono">X-CAS-Signature</code> header. Verify it before processing the payload.</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <i class="fab fa-php text-indigo-500 text-sm mr-2"></i>
            <span class="text-xs font-medium text-slate-600">PHP verification example</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-red-300">$payload</span>   = <span class="text-green-400">file_get_contents</span>(<span class="text-amber-300">'php://input'</span>);
<span class="text-red-300">$signature</span> = <span class="text-red-300">$_SERVER</span>[<span class="text-amber-300">'HTTP_X_CAS_SIGNATURE'</span>];
<span class="text-red-300">$expected</span>  = <span class="text-green-400">hash_hmac</span>(<span class="text-amber-300">'sha256'</span>, <span class="text-red-300">$payload</span>, <span class="text-red-300">$webhookSecret</span>);

<span class="text-violet-400">if</span> (<span class="text-green-400">hash_equals</span>(<span class="text-red-300">$expected</span>, <span class="text-red-300">$signature</span>)) {
    <span class="text-slate-500">// Safe to process</span>
    <span class="text-red-300">$event</span> = <span class="text-green-400">json_decode</span>(<span class="text-red-300">$payload</span>, <span class="text-blue-400">true</span>);
}</code></pre>
        </div>
    </div>
</section>

{{-- Configuration --}}
<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Registering a Webhook</h2>
    <p class="text-sm text-slate-600 leading-relaxed mb-6">Register webhook endpoints from the CAS Admin Panel under <strong>Settings &rarr; Webhooks</strong>, or via the API:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="inline-flex items-center px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded mr-2">POST</span>
            <span class="text-xs font-medium text-slate-600">/api/webhooks</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "<span class="text-amber-300">url</span>": "<span class="text-green-400">https://your-app.com/webhooks/cas</span>",
  "<span class="text-amber-300">events</span>": [<span class="text-green-400">"user.login"</span>, <span class="text-green-400">"user.logout"</span>, <span class="text-green-400">"user.locked"</span>],
  "<span class="text-amber-300">secret</span>": "<span class="text-green-400">whsec_your_signing_secret</span>"
}</code></pre>
        </div>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
            <div class="text-sm text-amber-800">
                <strong>Security</strong> — Always verify the <code class="text-xs bg-amber-100 px-1 py-0.5 rounded font-mono">X-CAS-Signature</code> header before processing events. Never trust unverified payloads.
            </div>
        </div>
    </div>
</section>
@endsection
