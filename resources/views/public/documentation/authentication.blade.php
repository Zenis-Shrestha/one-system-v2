@extends('public.documentation.layout')

@section('title', 'Authentication Flows — CAS SSO')
@section('description', 'Detailed guide to CAS SSO authentication flows, JWT token management, and security features.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">Advanced Topics</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">Authentication Flows</h1>
        <p class="text-lg text-slate-500 leading-relaxed">How tokens are generated, validated, and managed across client applications.</p>
    </div>
</section>

{{-- SSO Flow --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">SSO Login Flow</h2>
    <div class="space-y-0">
        <div class="flex gap-4">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">1</div>
                <div class="w-px h-full bg-slate-200"></div>
            </div>
            <div class="pb-8">
                <h3 class="text-sm font-semibold text-slate-900 mb-1">User hits protected route</h3>
                <p class="text-sm text-slate-500">The CAS middleware detects no valid session and redirects the user to the CAS login page.</p>
            </div>
        </div>
        <div class="flex gap-4">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">2</div>
                <div class="w-px h-full bg-slate-200"></div>
            </div>
            <div class="pb-8">
                <h3 class="text-sm font-semibold text-slate-900 mb-1">CAS authenticates the user</h3>
                <p class="text-sm text-slate-500">The CAS server validates credentials, checks reCAPTCHA, enforces lockout rules, and (optionally) verifies 2FA.</p>
            </div>
        </div>
        <div class="flex gap-4">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">3</div>
                <div class="w-px h-full bg-slate-200"></div>
            </div>
            <div class="pb-8">
                <h3 class="text-sm font-semibold text-slate-900 mb-1">JWT token issued</h3>
                <p class="text-sm text-slate-500">On success, the server generates a signed JWT containing user claims and redirects back to the client callback URL.</p>
            </div>
        </div>
        <div class="flex gap-4">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">4</div>
                <div class="w-px h-full bg-slate-200"></div>
            </div>
            <div class="pb-8">
                <h3 class="text-sm font-semibold text-slate-900 mb-1">Client validates token</h3>
                <p class="text-sm text-slate-500">The client application calls <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">POST /sso/validate</code> with the token to verify authenticity and extract user data.</p>
            </div>
        </div>
        <div class="flex gap-4">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 bg-emerald-600 text-white rounded-full flex items-center justify-center text-xs font-bold">5</div>
            </div>
            <div class="pb-4">
                <h3 class="text-sm font-semibold text-slate-900 mb-1">Session established</h3>
                <p class="text-sm text-slate-500">The client creates a local session using the validated user data. The user is now authenticated.</p>
            </div>
        </div>
    </div>
</section>

{{-- Token Lifecycle --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Token Lifecycle</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center mb-3"><i class="fas fa-plus-circle text-blue-600 text-sm"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Issued</h3>
            <p class="text-xs text-slate-500">Generated after successful authentication. Signed with HMAC-SHA256. Default TTL: 60 minutes.</p>
        </div>
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center mb-3"><i class="fas fa-sync-alt text-amber-600 text-sm"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Validated</h3>
            <p class="text-xs text-slate-500">Client applications verify tokens via the <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">/sso/validate</code> endpoint before granting access.</p>
        </div>
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="w-9 h-9 bg-red-50 rounded-lg flex items-center justify-center mb-3"><i class="fas fa-times-circle text-red-600 text-sm"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Expired / Revoked</h3>
            <p class="text-xs text-slate-500">Tokens expire after TTL or when explicitly revoked via logout. Expired tokens return <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">401</code>.</p>
        </div>
    </div>
</section>

{{-- 2FA Flow --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Two-Factor Authentication</h2>
    <p class="text-sm text-slate-600 mb-4">When 2FA is enabled, the login flow includes an additional verification step:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-slate-500">// Step 1: Initial login returns 2FA challenge</span>
{
  "<span class="text-amber-300">requires_2fa</span>": <span class="text-blue-400">true</span>,
  "<span class="text-amber-300">temp_token</span>": "<span class="text-green-400">temp_eyJhbGci...</span>"
}

<span class="text-slate-500">// Step 2: Submit TOTP code with temp token</span>
POST /sso/verify-2fa
{
  "<span class="text-amber-300">temp_token</span>": "<span class="text-green-400">temp_eyJhbGci...</span>",
  "<span class="text-amber-300">totp_code</span>": "<span class="text-green-400">123456</span>"
}

<span class="text-slate-500">// Step 3: Receive full JWT token</span>
{
  "<span class="text-amber-300">success</span>": <span class="text-blue-400">true</span>,
  "<span class="text-amber-300">token</span>": "<span class="text-green-400">eyJhbGciOiJIUzI1NiIs...</span>"
}</code></pre>
        </div>
    </div>
</section>

{{-- Logout --}}
<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Logout &amp; Session Termination</h2>
    <p class="text-sm text-slate-600 mb-4">CAS supports single-logout — logging out from one application invalidates the token across all connected systems.</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="inline-flex items-center px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded">POST</span>
            <span class="text-xs font-medium text-slate-600">/api/logout</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "<span class="text-amber-300">token</span>": "<span class="text-green-400">eyJhbGciOiJIUzI1NiIs...</span>",
  "<span class="text-amber-300">logout_all</span>": <span class="text-blue-400">true</span>  <span class="text-slate-500">// invalidate across all clients</span>
}</code></pre>
        </div>
    </div>
</section>
@endsection