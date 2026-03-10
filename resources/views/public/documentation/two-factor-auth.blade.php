@extends('public.documentation.layout')

@section('title', 'Two-Factor Authentication Setup — CAS SSO')
@section('description', 'How to enable and use TOTP-based two-factor authentication in CAS SSO.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">How To Use</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">Two-Factor Authentication</h1>
        <p class="text-lg text-slate-500 leading-relaxed">Enable TOTP-based 2FA to add an extra layer of security to your CAS accounts.</p>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-slate-200 bg-slate-50/50">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">On This Page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#overview" class="text-blue-600">1. How 2FA Works</a></li>
        <li><a href="#enable" class="text-blue-600">2. Enabling 2FA</a></li>
        <li><a href="#login" class="text-blue-600">3. Logging In with 2FA</a></li>
        <li><a href="#recovery" class="text-blue-600">4. Recovery Codes</a></li>
        <li><a href="#api" class="text-blue-600">5. API Integration</a></li>
    </ol>
</nav>

<section id="overview" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">1. How 2FA Works</h2>
    <p class="text-sm text-slate-600 mb-4">CAS uses Time-based One-Time Passwords (TOTP) for two-factor authentication. After entering your password, you provide a 6-digit code from your authenticator app.</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-5 rounded-xl border border-slate-200 text-center">
            <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-3"><i class="fas fa-lock text-blue-600"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Password</h3>
            <p class="text-xs text-slate-500">Something you know</p>
        </div>
        <div class="p-5 rounded-xl border border-slate-200 text-center">
            <div class="flex justify-center mb-3"><i class="fas fa-plus text-slate-300 text-xl mt-3"></i></div>
            <p class="text-xs text-slate-400 font-medium">COMBINED WITH</p>
        </div>
        <div class="p-5 rounded-xl border border-slate-200 text-center">
            <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3"><i class="fas fa-mobile-alt text-emerald-600"></i></div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">TOTP Code</h3>
            <p class="text-xs text-slate-500">Something you have</p>
        </div>
    </div>
    <div class="mt-4">
        <p class="text-xs text-slate-500"><strong>Compatible apps:</strong> Google Authenticator, Authy, Microsoft Authenticator, 1Password</p>
    </div>
</section>

<section id="enable" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">2. Enabling 2FA</h2>
    <div class="space-y-0 mb-6">
        <div class="flex gap-4">
            <div class="flex flex-col items-center"><div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">1</div><div class="w-px h-full bg-slate-200"></div></div>
            <div class="pb-6"><h3 class="text-sm font-semibold text-slate-900">Navigate to Security Settings</h3><p class="text-xs text-slate-500">Go to <strong>User Portal → Profile → Security</strong> or <strong>Admin → Users → Edit → 2FA</strong></p></div>
        </div>
        <div class="flex gap-4">
            <div class="flex flex-col items-center"><div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">2</div><div class="w-px h-full bg-slate-200"></div></div>
            <div class="pb-6"><h3 class="text-sm font-semibold text-slate-900">Scan the QR Code</h3><p class="text-xs text-slate-500">Open your authenticator app and scan the displayed QR code. If you can't scan, use the manual secret key.</p></div>
        </div>
        <div class="flex gap-4">
            <div class="flex flex-col items-center"><div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">3</div><div class="w-px h-full bg-slate-200"></div></div>
            <div class="pb-6"><h3 class="text-sm font-semibold text-slate-900">Enter Verification Code</h3><p class="text-xs text-slate-500">Enter the 6-digit code from your authenticator app to confirm setup.</p></div>
        </div>
        <div class="flex gap-4">
            <div class="flex flex-col items-center"><div class="w-8 h-8 bg-emerald-600 text-white rounded-full flex items-center justify-center text-xs font-bold">4</div></div>
            <div class="pb-4"><h3 class="text-sm font-semibold text-slate-900">Save Recovery Codes</h3><p class="text-xs text-slate-500">Download or copy the backup recovery codes. Store them securely — they're your fallback if you lose your device.</p></div>
        </div>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
            <span class="text-sm text-amber-800"><strong>Important:</strong> Recovery codes are shown only once during setup. Store them in a secure location like a password manager.</span>
        </div>
    </div>
</section>

<section id="login" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">3. Logging In with 2FA</h2>
    <p class="text-sm text-slate-600 mb-4">When 2FA is enabled, the login flow adds an extra step:</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="p-4 rounded-xl border border-slate-200">
            <div class="text-xs font-bold text-blue-600 mb-2">Step 1</div>
            <p class="text-sm font-semibold text-slate-900 mb-1">Enter Credentials</p>
            <p class="text-xs text-slate-500">Email and password as usual</p>
        </div>
        <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/50">
            <div class="text-xs font-bold text-blue-600 mb-2">Step 2</div>
            <p class="text-sm font-semibold text-slate-900 mb-1">2FA Challenge</p>
            <p class="text-xs text-slate-500">Enter 6-digit TOTP code</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <div class="text-xs font-bold text-emerald-600 mb-2">Step 3</div>
            <p class="text-sm font-semibold text-slate-900 mb-1">Access Granted</p>
            <p class="text-xs text-slate-500">Full JWT token issued</p>
        </div>
    </div>
</section>

<section id="recovery" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">4. Recovery Codes</h2>
    <p class="text-sm text-slate-600 mb-4">Each recovery code is single-use. If you've lost your authenticator device:</p>
    <ol class="space-y-2 text-sm text-slate-600 mb-4">
        <li class="flex items-start gap-2"><span class="font-bold text-slate-900">1.</span> On the 2FA challenge screen, click "Use Recovery Code"</li>
        <li class="flex items-start gap-2"><span class="font-bold text-slate-900">2.</span> Enter one of your saved recovery codes</li>
        <li class="flex items-start gap-2"><span class="font-bold text-slate-900">3.</span> After login, immediately set up 2FA again with a new device</li>
    </ol>
    <p class="text-sm text-slate-600">If you've lost both your device and recovery codes, contact an admin to disable 2FA on your account.</p>
</section>

<section id="api" class="border-t border-slate-200 pt-10">
    <h2 class="text-xl font-bold text-slate-900 mb-4">5. API Integration</h2>
    <p class="text-sm text-slate-600 mb-4">When a 2FA-enabled user authenticates via API, the flow uses a temporary token:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">Step 1 — Initial login returns 2FA requirement</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300 leading-relaxed"><code>// Response from POST /api/sso/token
{
  "requires_2fa": true,
  "temp_token": "temp_eyJhbGci..."
}</code></pre>
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">Step 2 — Submit TOTP code</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300 leading-relaxed"><code>POST /api/sso/verify-2fa
{
  "temp_token": "temp_eyJhbGci...",
  "totp_code": "123456"
}</code></pre>
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">Step 3 — Full token received</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300 leading-relaxed"><code>{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIs..."
}</code></pre>
        </div>
    </div>
</section>
@endsection
