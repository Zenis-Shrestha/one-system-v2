@extends('public.documentation.layout')

@section('title', 'Changelog — CAS SSO')
@section('description', 'Version history and release notes for CAS Single Sign-On authentication system.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">Technical Reference</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">Changelog</h1>
        <p class="text-lg text-slate-500 leading-relaxed">All notable changes to the CAS SSO platform are documented here.</p>
    </div>
</section>

{{-- v2.1.0 --}}
<section class="mb-12">
    <div class="flex items-start gap-4 mb-6">
        <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 text-sm font-bold rounded-full flex-shrink-0">v2.1.0</span>
        <div>
            <h2 class="text-xl font-bold text-slate-900">Security Hardening &amp; Webhook Support</h2>
            <p class="text-sm text-slate-400 mt-1">March 2026</p>
        </div>
    </div>
    <div class="ml-16 space-y-4">
        <div>
            <h3 class="text-xs font-semibold text-emerald-600 uppercase tracking-widest mb-2">Added</h3>
            <ul class="space-y-1.5 text-sm text-slate-600">
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> Webhook event system for real-time authentication notifications</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> SDK download page with versioned package management</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> reCAPTCHA v3 integration on login endpoints</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> Account lockout system — 5 failed attempts triggers 30-minute cooldown</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> HMAC-SHA256 request signature verification for all API calls</li>
            </ul>
        </div>
        <div>
            <h3 class="text-xs font-semibold text-blue-600 uppercase tracking-widest mb-2">Improved</h3>
            <ul class="space-y-1.5 text-sm text-slate-600">
                <li class="flex items-start gap-2"><span class="text-blue-500 mt-0.5">~</span> JWT token payload now includes <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">security_features</code> object</li>
                <li class="flex items-start gap-2"><span class="text-blue-500 mt-0.5">~</span> Rate limiting granularity — separate limits per endpoint category</li>
                <li class="flex items-start gap-2"><span class="text-blue-500 mt-0.5">~</span> Documentation site redesigned with modern, professional layout</li>
            </ul>
        </div>
    </div>
</section>

{{-- v2.0.0 --}}
<section class="mb-12">
    <div class="flex items-start gap-4 mb-6">
        <span class="inline-flex items-center px-3 py-1 bg-slate-100 text-slate-700 text-sm font-bold rounded-full flex-shrink-0">v2.0.0</span>
        <div>
            <h2 class="text-xl font-bold text-slate-900">Enterprise Release</h2>
            <p class="text-sm text-slate-400 mt-1">January 2026</p>
        </div>
    </div>
    <div class="ml-16 space-y-4">
        <div>
            <h3 class="text-xs font-semibold text-emerald-600 uppercase tracking-widest mb-2">Added</h3>
            <ul class="space-y-1.5 text-sm text-slate-600">
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> Multi-platform SSO with Laravel, .NET, Node.js, Java, Python, JavaScript SDKs</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> Admin dashboard with real-time user monitoring and audit logs</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> User self-service portal for profile management and 2FA setup</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> Client system registration with IP whitelisting</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> PostgreSQL multi-schema database architecture</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> Docker-based deployment with Kubernetes support</li>
            </ul>
        </div>
        <div>
            <h3 class="text-xs font-semibold text-red-600 uppercase tracking-widest mb-2">Breaking Changes</h3>
            <ul class="space-y-1.5 text-sm text-slate-600">
                <li class="flex items-start gap-2"><span class="text-red-500 mt-0.5">!</span> Token endpoint moved from <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">/api/token</code> to <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">/api/sso/token</code></li>
                <li class="flex items-start gap-2"><span class="text-red-500 mt-0.5">!</span> HMAC signature header required on all authenticated endpoints</li>
                <li class="flex items-start gap-2"><span class="text-red-500 mt-0.5">!</span> Client credentials now require <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">client_username</code> and <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">client_password</code></li>
            </ul>
        </div>
    </div>
</section>

{{-- v1.5.0 --}}
<section class="mb-12">
    <div class="flex items-start gap-4 mb-6">
        <span class="inline-flex items-center px-3 py-1 bg-slate-100 text-slate-700 text-sm font-bold rounded-full flex-shrink-0">v1.5.0</span>
        <div>
            <h2 class="text-xl font-bold text-slate-900">Two-Factor Authentication</h2>
            <p class="text-sm text-slate-400 mt-1">October 2025</p>
        </div>
    </div>
    <div class="ml-16 space-y-4">
        <div>
            <h3 class="text-xs font-semibold text-emerald-600 uppercase tracking-widest mb-2">Added</h3>
            <ul class="space-y-1.5 text-sm text-slate-600">
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> TOTP-based two-factor authentication with QR code setup</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> Backup recovery codes for 2FA</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> Session management with device-level tracking</li>
            </ul>
        </div>
        <div>
            <h3 class="text-xs font-semibold text-blue-600 uppercase tracking-widest mb-2">Improved</h3>
            <ul class="space-y-1.5 text-sm text-slate-600">
                <li class="flex items-start gap-2"><span class="text-blue-500 mt-0.5">~</span> Password hashing upgraded to bcrypt with 12 rounds</li>
                <li class="flex items-start gap-2"><span class="text-blue-500 mt-0.5">~</span> Login audit log now captures user agent and geo-IP data</li>
            </ul>
        </div>
    </div>
</section>

{{-- v1.0.0 --}}
<section class="mb-12">
    <div class="flex items-start gap-4 mb-6">
        <span class="inline-flex items-center px-3 py-1 bg-slate-100 text-slate-700 text-sm font-bold rounded-full flex-shrink-0">v1.0.0</span>
        <div>
            <h2 class="text-xl font-bold text-slate-900">Initial Release</h2>
            <p class="text-sm text-slate-400 mt-1">August 2025</p>
        </div>
    </div>
    <div class="ml-16 space-y-4">
        <div>
            <h3 class="text-xs font-semibold text-emerald-600 uppercase tracking-widest mb-2">Added</h3>
            <ul class="space-y-1.5 text-sm text-slate-600">
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> Core SSO authentication via JWT tokens</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> User registration and login</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> Laravel client package</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> Basic admin panel for user management</li>
                <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">+</span> Token generation and validation endpoints</li>
            </ul>
        </div>
    </div>
</section>
@endsection
