@extends('public.documentation.layout')

@section('title', 'Troubleshooting — CAS SSO')
@section('description', 'Common issues and solutions for CAS Single Sign-On authentication system.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">Advanced Topics</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">Troubleshooting</h1>
        <p class="text-lg text-slate-500 leading-relaxed">Solutions for common integration issues and error scenarios.</p>
    </div>
</section>

{{-- Authentication Issues --}}
<section class="mb-12">
    <h2 class="text-lg font-bold text-slate-900 mb-4">Authentication Issues</h2>
    <div class="space-y-4">
        <details class="group rounded-xl border border-slate-200 overflow-hidden">
            <summary class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-slate-50 transition-colors">
                <span class="text-sm font-semibold text-slate-900">Invalid HMAC signature error</span>
                <i class="fas fa-chevron-down text-slate-400 text-xs group-open:rotate-180 transition-transform"></i>
            </summary>
            <div class="px-5 pb-4 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
                <p class="mb-2">This occurs when the request signature doesn't match the server's calculation.</p>
                <ul class="space-y-1.5 ml-4">
                    <li>Verify your <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">client_secret</code> matches the value in the admin panel</li>
                    <li>Ensure the timestamp in <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">X-Timestamp</code> is within 5 minutes of server time</li>
                    <li>The signature payload must be: <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">timestamp + "." + body</code></li>
                    <li>Check for encoding differences — use UTF-8 consistently</li>
                </ul>
            </div>
        </details>

        <details class="group rounded-xl border border-slate-200 overflow-hidden">
            <summary class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-slate-50 transition-colors">
                <span class="text-sm font-semibold text-slate-900">Account locked — 423 response</span>
                <i class="fas fa-chevron-down text-slate-400 text-xs group-open:rotate-180 transition-transform"></i>
            </summary>
            <div class="px-5 pb-4 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
                <p class="mb-2">Accounts are locked after 5 failed login attempts.</p>
                <ul class="space-y-1.5 ml-4">
                    <li>Wait 30 minutes for automatic unlock</li>
                    <li>Ask an admin to manually unlock from <strong>Admin Panel → Users</strong></li>
                    <li>The response includes <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">remaining_minutes</code> until auto-unlock</li>
                </ul>
            </div>
        </details>

        <details class="group rounded-xl border border-slate-200 overflow-hidden">
            <summary class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-slate-50 transition-colors">
                <span class="text-sm font-semibold text-slate-900">Token expired immediately after issue</span>
                <i class="fas fa-chevron-down text-slate-400 text-xs group-open:rotate-180 transition-transform"></i>
            </summary>
            <div class="px-5 pb-4 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
                <p class="mb-2">Usually caused by clock skew between the CAS server and your application.</p>
                <ul class="space-y-1.5 ml-4">
                    <li>Sync server clocks with NTP</li>
                    <li>Allow a 60-second tolerance when validating <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">exp</code> claims</li>
                    <li>Check the <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">token_ttl</code> configuration value</li>
                </ul>
            </div>
        </details>
    </div>
</section>

{{-- Connection Issues --}}
<section class="mb-12">
    <h2 class="text-lg font-bold text-slate-900 mb-4">Connection Issues</h2>
    <div class="space-y-4">
        <details class="group rounded-xl border border-slate-200 overflow-hidden">
            <summary class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-slate-50 transition-colors">
                <span class="text-sm font-semibold text-slate-900">Connection refused to CAS server</span>
                <i class="fas fa-chevron-down text-slate-400 text-xs group-open:rotate-180 transition-transform"></i>
            </summary>
            <div class="px-5 pb-4 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
                <ul class="space-y-1.5 ml-4">
                    <li>Verify the <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">CAS_SERVER_URL</code> environment variable</li>
                    <li>Check firewall rules — port 8000 (or your configured port) must be open</li>
                    <li>If using Docker, ensure containers are on the same network</li>
                    <li>Test connectivity: <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">curl https://your-cas-server.com/api/health</code></li>
                </ul>
            </div>
        </details>

        <details class="group rounded-xl border border-slate-200 overflow-hidden">
            <summary class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-slate-50 transition-colors">
                <span class="text-sm font-semibold text-slate-900">SSL certificate errors</span>
                <i class="fas fa-chevron-down text-slate-400 text-xs group-open:rotate-180 transition-transform"></i>
            </summary>
            <div class="px-5 pb-4 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
                <ul class="space-y-1.5 ml-4">
                    <li>Ensure your SSL certificate is valid and not expired</li>
                    <li>For local development, add the self-signed CA to your trust store</li>
                    <li>Verify the certificate covers your domain (check SANs)</li>
                    <li>In PHP: set <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">CURLOPT_CAINFO</code> to your CA bundle path</li>
                </ul>
            </div>
        </details>

        <details class="group rounded-xl border border-slate-200 overflow-hidden">
            <summary class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-slate-50 transition-colors">
                <span class="text-sm font-semibold text-slate-900">Database connection timeout</span>
                <i class="fas fa-chevron-down text-slate-400 text-xs group-open:rotate-180 transition-transform"></i>
            </summary>
            <div class="px-5 pb-4 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
                <ul class="space-y-1.5 ml-4">
                    <li>Check <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">DB_HOST</code> and <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">DB_PORT</code> values</li>
                    <li>Verify PostgreSQL is running: <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">pg_isready</code></li>
                    <li>Check max connections: <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">SHOW max_connections;</code></li>
                    <li>Consider connection pooling with PgBouncer for high traffic</li>
                </ul>
            </div>
        </details>
    </div>
</section>

{{-- Rate Limiting --}}
<section class="mb-12">
    <h2 class="text-lg font-bold text-slate-900 mb-4">Rate Limiting</h2>
    <div class="space-y-4">
        <details class="group rounded-xl border border-slate-200 overflow-hidden" open>
            <summary class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-slate-50 transition-colors">
                <span class="text-sm font-semibold text-slate-900">429 Too Many Requests</span>
                <i class="fas fa-chevron-down text-slate-400 text-xs group-open:rotate-180 transition-transform"></i>
            </summary>
            <div class="px-5 pb-4 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
                <p class="mb-2">Your application is exceeding the rate limit. Check the <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">Retry-After</code> response header.</p>
                <div class="rounded-lg bg-slate-900 p-4 mt-3">
                    <pre class="text-xs font-mono text-slate-300"><code><span class="text-slate-500">// Implement exponential backoff</span>
<span class="text-violet-400">const</span> delay = <span class="text-green-400">Math.pow</span>(<span class="text-blue-400">2</span>, retryCount) * <span class="text-blue-400">1000</span>;
<span class="text-violet-400">await new</span> <span class="text-green-400">Promise</span>(r => <span class="text-green-400">setTimeout</span>(r, delay));</code></pre>
                </div>
            </div>
        </details>
    </div>
</section>

{{-- Debugging --}}
<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Debug Commands</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-slate-500"># Check CAS server health</span>
curl https://your-cas-server.com/api/health

<span class="text-slate-500"># View Laravel logs</span>
tail -f storage/logs/laravel.log

<span class="text-slate-500"># Clear all caches</span>
php artisan cache:clear && php artisan config:clear

<span class="text-slate-500"># Test database connection</span>
php artisan db:show</code></pre>
        </div>
    </div>
</section>

{{-- Contact & Support --}}
<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Contact & Support</h2>
    <p class="text-sm text-slate-600 mb-6">If the above solutions don't resolve your issue, reach out to the CAS development team.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center"><i class="fas fa-envelope text-blue-600 text-sm"></i></div>
                <h3 class="text-sm font-semibold text-slate-900">Email Support</h3>
            </div>
            <p class="text-sm text-slate-600 mb-2">For technical issues, integration help, or bug reports:</p>
            <a href="https://innovativesolution.com.np/" target="_blank" class="text-sm font-semibold text-blue-600 hover:text-blue-800">innovativesolution.com.np</a>
            <p class="text-xs text-slate-500 mt-2">Expected response time: <strong>within 24 hours</strong> on business days.</p>
        </div>
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center"><i class="fas fa-building text-emerald-600 text-sm"></i></div>
                <h3 class="text-sm font-semibold text-slate-900">Development Team</h3>
            </div>
            <p class="text-sm text-slate-600 mb-2">CAS is developed and maintained by:</p>
            <p class="text-sm font-semibold text-slate-900">Innovative Solution Pvt. Ltd.</p>
            <p class="text-xs text-slate-500 mt-1">Lalitpur, Nepal</p>
        </div>
    </div>

    <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50">
        <h3 class="text-sm font-semibold text-slate-900 mb-3"><i class="fas fa-bug text-amber-500 mr-2"></i>How to Report an Issue</h3>
        <p class="text-xs text-slate-600 mb-3">When reporting an issue, please include the following details for faster resolution:</p>
        <ol class="space-y-2 text-xs text-slate-600">
            <li class="flex items-start gap-2"><span class="font-bold text-slate-900">1.</span> <strong>Description</strong> — What happened vs. what you expected</li>
            <li class="flex items-start gap-2"><span class="font-bold text-slate-900">2.</span> <strong>Steps to reproduce</strong> — How to trigger the issue</li>
            <li class="flex items-start gap-2"><span class="font-bold text-slate-900">3.</span> <strong>Error messages</strong> — Full error text or HTTP status codes</li>
            <li class="flex items-start gap-2"><span class="font-bold text-slate-900">4.</span> <strong>Environment</strong> — Language/framework, SDK version, CAS server version</li>
            <li class="flex items-start gap-2"><span class="font-bold text-slate-900">5.</span> <strong>Logs</strong> — Relevant entries from <code class="bg-slate-100 px-1 rounded">storage/logs/laravel.log</code> (server) or your application logs (client)</li>
        </ol>
    </div>
</section>
@endsection