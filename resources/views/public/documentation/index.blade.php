@extends('public.documentation.layout')

@section('title', 'CAS SSO Integration Documentation')
@section('description', 'Complete integration guide for CAS Single Sign-On authentication system supporting Laravel, .NET, Node.js, Java, Python, and more.')

@section('content')
{{-- Hero —— lightweight header that sits naturally inside the sidebar layout --}}
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">Documentation</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">
            CAS SSO Integration Guide
        </h1>
        <p class="text-lg text-slate-500 leading-relaxed mb-8">
            Everything you need to integrate your applications with our Central Authentication Service.
            Choose your platform, follow the guide, and go live in minutes.
        </p>
        <div class="flex flex-wrap gap-3">
            <a href="#platforms" class="inline-flex items-center px-5 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition-colors">
                <i class="fas fa-rocket mr-2 text-xs"></i>Get Started
            </a>
            <a href="{{ route('docs.api.overview') }}" class="inline-flex items-center px-5 py-2.5 bg-white text-slate-700 text-sm font-medium rounded-lg border border-slate-300 hover:bg-slate-50 hover:border-slate-400 transition-colors">
                <i class="fas fa-code mr-2 text-xs"></i>API Reference
            </a>
        </div>
    </div>
</section>

{{-- Why CAS SSO —— three compact value props --}}
<section class="mb-16">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-6">Why CAS SSO</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="group p-5 rounded-xl border border-slate-200 hover:border-slate-300 transition-colors">
            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mb-4">
                <i class="fas fa-lock text-blue-600 text-sm"></i>
            </div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1.5">Secure Authentication</h3>
            <p class="text-sm text-slate-500 leading-relaxed">JWT tokens with HMAC&#8209;SHA256 signatures and configurable expiration policies.</p>
        </div>
        <div class="group p-5 rounded-xl border border-slate-200 hover:border-slate-300 transition-colors">
            <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center mb-4">
                <i class="fas fa-cogs text-emerald-600 text-sm"></i>
            </div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1.5">Multi-Platform</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Native packages for Laravel, .NET, Node.js, Java, Python, and vanilla JS.</p>
        </div>
        <div class="group p-5 rounded-xl border border-slate-200 hover:border-slate-300 transition-colors">
            <div class="w-10 h-10 bg-violet-50 rounded-lg flex items-center justify-center mb-4">
                <i class="fas fa-chart-line text-violet-600 text-sm"></i>
            </div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1.5">Audit &amp; Monitoring</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Full audit trail with IP tracking, user agent logging, and real-time dashboards.</p>
        </div>
    </div>
</section>

{{-- Architecture —— two side-by-side cards --}}
<section class="mb-16">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-6">Architecture</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('docs.architecture') }}" class="group block p-6 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50/30 transition-all">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-sitemap text-blue-600 text-sm"></i>
                </div>
                <h3 class="text-base font-semibold text-slate-900">System Architecture</h3>
            </div>
            <p class="text-sm text-slate-500 leading-relaxed mb-3">Admin/User/Public separation, Livewire components, and the comprehensive security layer.</p>
            <span class="text-sm font-medium text-blue-600 group-hover:text-blue-700 inline-flex items-center">
                Read more <i class="fas fa-arrow-right ml-1.5 text-xs group-hover:translate-x-0.5 transition-transform"></i>
            </span>
        </a>
        <a href="{{ route('docs.architecture') }}#database-schema" class="group block p-6 rounded-xl border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/30 transition-all">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-database text-emerald-600 text-sm"></i>
                </div>
                <h3 class="text-base font-semibold text-slate-900">Database Schema</h3>
            </div>
            <p class="text-sm text-slate-500 leading-relaxed mb-3">PostgreSQL multi-schema design with security isolation, access controls, and audit trails.</p>
            <span class="text-sm font-medium text-emerald-600 group-hover:text-emerald-700 inline-flex items-center">
                Read more <i class="fas fa-arrow-right ml-1.5 text-xs group-hover:translate-x-0.5 transition-transform"></i>
            </span>
        </a>
    </div>
</section>

{{-- Platform Guides —— the main grid --}}
<section id="platforms" class="mb-16">
    <div class="flex items-end justify-between mb-6">
        <div>
            <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Integration Guides</h2>
            <p class="text-sm text-slate-500">Choose your stack and follow the step-by-step guide.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {{-- Laravel --}}
        <a href="{{ route('docs.laravel') }}" class="group flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-red-200 hover:bg-red-50/30 transition-all">
            <div class="w-11 h-11 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fab fa-laravel text-red-600 text-lg"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Laravel</span>
                    <span class="text-[10px] font-medium text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded">Popular</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Composer package &middot; 5 min</p>
            </div>
            <i class="fas fa-chevron-right text-slate-300 text-xs group-hover:text-slate-500 transition-colors"></i>
        </a>

        {{-- .NET --}}
        <a href="{{ route('docs.dotnet') }}" class="group flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-blue-200 hover:bg-blue-50/30 transition-all">
            <div class="w-11 h-11 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fab fa-microsoft text-blue-600 text-lg"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-sm font-semibold text-slate-900">.NET MVC</span>
                <p class="text-xs text-slate-500 mt-0.5">C# &middot; NuGet &middot; 10 min</p>
            </div>
            <i class="fas fa-chevron-right text-slate-300 text-xs group-hover:text-slate-500 transition-colors"></i>
        </a>

        {{-- Node.js --}}
        <a href="{{ route('docs.nodejs') }}" class="group flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-green-200 hover:bg-green-50/30 transition-all">
            <div class="w-11 h-11 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                <i class="fab fa-node-js text-green-600 text-lg"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Node.js</span>
                    <span class="text-[10px] font-medium text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded">Popular</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Express middleware &middot; 3 min</p>
            </div>
            <i class="fas fa-chevron-right text-slate-300 text-xs group-hover:text-slate-500 transition-colors"></i>
        </a>

        {{-- Java --}}
        <a href="{{ route('docs.java') }}" class="group flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-orange-200 hover:bg-orange-50/30 transition-all">
            <div class="w-11 h-11 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                <i class="fab fa-java text-orange-600 text-lg"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-sm font-semibold text-slate-900">Java Spring</span>
                <p class="text-xs text-slate-500 mt-0.5">Spring Security &middot; 8 min</p>
            </div>
            <i class="fas fa-chevron-right text-slate-300 text-xs group-hover:text-slate-500 transition-colors"></i>
        </a>

        {{-- Python --}}
        <a href="{{ route('docs.python') }}" class="group flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-indigo-200 hover:bg-indigo-50/30 transition-all">
            <div class="w-11 h-11 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                <i class="fab fa-python text-indigo-600 text-lg"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-sm font-semibold text-slate-900">Python Django</span>
                <p class="text-xs text-slate-500 mt-0.5">Django middleware &middot; 7 min</p>
            </div>
            <i class="fas fa-chevron-right text-slate-300 text-xs group-hover:text-slate-500 transition-colors"></i>
        </a>

        {{-- JavaScript --}}
        <a href="{{ route('docs.javascript') }}" class="group flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-yellow-200 hover:bg-yellow-50/30 transition-all">
            <div class="w-11 h-11 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0">
                <i class="fab fa-js text-yellow-600 text-lg"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-sm font-semibold text-slate-900">JavaScript</span>
                <p class="text-xs text-slate-500 mt-0.5">Frontend / SPA &middot; 5 min</p>
            </div>
            <i class="fas fa-chevron-right text-slate-300 text-xs group-hover:text-slate-500 transition-colors"></i>
        </a>
    </div>
</section>

{{-- Quick Start —— three numbered steps, clean horizontal layout --}}
<section class="mb-16">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-6">Quick Start</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="relative pl-10">
            <span class="absolute left-0 top-0 w-7 h-7 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">1</span>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Register Your App</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Register with the CAS admin panel to receive your client ID and secret.</p>
        </div>
        <div class="relative pl-10">
            <span class="absolute left-0 top-0 w-7 h-7 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">2</span>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Install the Package</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Install the SDK for your platform via Composer, npm, pip, or NuGet.</p>
        </div>
        <div class="relative pl-10">
            <span class="absolute left-0 top-0 w-7 h-7 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">3</span>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Configure &amp; Test</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Set your environment variables, add the middleware, and verify the auth flow.</p>
        </div>
    </div>
</section>

{{-- Code Example —— compact, well-formatted code block --}}
<section class="mb-16">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-6">Example &mdash; Laravel</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <div class="flex items-center gap-2">
                <i class="fab fa-laravel text-red-500 text-sm"></i>
                <span class="text-xs font-medium text-slate-600">routes/web.php</span>
            </div>
            <button onclick="copyCode()" id="copy-btn" class="text-xs text-slate-400 hover:text-slate-600 transition-colors flex items-center gap-1">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre id="code-block" class="text-sm leading-relaxed font-mono"><code><span class="text-slate-500">// Install the package</span>
<span class="text-blue-400">composer require</span> <span class="text-amber-300">cas-system/laravel-client</span>

<span class="text-slate-500">// Protect routes with CAS middleware</span>
<span class="text-violet-400">Route</span>::<span class="text-green-400">middleware</span>([<span class="text-amber-300">'cas.auth'</span>])-><span class="text-green-400">group</span>(<span class="text-blue-400">function</span> () {
    <span class="text-violet-400">Route</span>::<span class="text-green-400">get</span>(<span class="text-amber-300">'/dashboard'</span>, [<span class="text-orange-300">DashboardController</span>::<span class="text-blue-400">class</span>, <span class="text-amber-300">'index'</span>]);
    <span class="text-violet-400">Route</span>::<span class="text-green-400">get</span>(<span class="text-amber-300">'/profile'</span>,   [<span class="text-orange-300">ProfileController</span>::<span class="text-blue-400">class</span>, <span class="text-amber-300">'show'</span>]);
});

<span class="text-slate-500">// Access the authenticated user</span>
<span class="text-red-300">$user</span> = <span class="text-green-400">session</span>(<span class="text-amber-300">'cas_user'</span>);</code></pre>
        </div>
    </div>
</section>

{{-- Additional Resources —— simple link list --}}
<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-6">More Resources</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-code text-slate-400 text-sm"></i>
            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">API Reference</span>
        </a>
        <a href="{{ route('docs.examples') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-play text-slate-400 text-sm"></i>
            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Code Examples</span>
        </a>
        <a href="{{ route('docs.security') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-shield-alt text-slate-400 text-sm"></i>
            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Security Guide</span>
        </a>
        <a href="/docs/troubleshooting" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-tools text-slate-400 text-sm"></i>
            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Troubleshooting</span>
        </a>
    </div>
</section>

<script>
function copyCode() {
    const code = document.getElementById('code-block').textContent;
    navigator.clipboard.writeText(code);
    const btn = document.getElementById('copy-btn');
    btn.innerHTML = '<i class="fas fa-check"></i> Copied';
    setTimeout(() => { btn.innerHTML = '<i class="fas fa-copy"></i> Copy'; }, 2000);
}
</script>
@endsection