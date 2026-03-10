@extends('public.documentation.layout')

@section('title', 'Admin Panel Guide — CAS SSO')
@section('description', 'How to use the CAS admin dashboard to manage users, client systems, and monitor activity.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">How To Use</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">Admin Panel Guide</h1>
        <p class="text-lg text-slate-500 leading-relaxed">Complete walkthrough of the CAS admin dashboard — manage everything from one place.</p>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-slate-200 bg-slate-50/50">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">On This Page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#access" class="text-blue-600">1. Accessing the Admin Panel</a></li>
        <li><a href="#dashboard" class="text-blue-600">2. Dashboard Overview</a></li>
        <li><a href="#clients" class="text-blue-600">3. Managing Client Systems</a></li>
        <li><a href="#users" class="text-blue-600">4. User Management</a></li>
        <li><a href="#audit" class="text-blue-600">5. Audit Logs</a></li>
        <li><a href="#ip" class="text-blue-600">6. IP Whitelist</a></li>
    </ol>
</nav>

{{-- Access --}}
<section id="access" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">1. Accessing the Admin Panel</h2>
    <p class="text-sm text-slate-600 mb-4">Login with an admin account at:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
        <div class="bg-slate-900 p-5"><pre class="text-sm font-mono text-slate-300"><code>https://your-cas-server.com/auth/login</code></pre></div>
    </div>
    <p class="text-sm text-slate-600">After login, admin users are automatically redirected to <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">/admin/dashboard</code>. Regular users go to the user portal instead.</p>
    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
            <span class="text-sm text-blue-800">Only users with the <code class="bg-blue-100 px-1 py-0.5 rounded font-mono text-xs">admin</code> role can access the admin panel. The role-based redirect happens automatically in the <code class="bg-blue-100 px-1 py-0.5 rounded font-mono text-xs">AuthController</code>.</span>
        </div>
    </div>
</section>

{{-- Dashboard --}}
<section id="dashboard" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">2. Dashboard Overview</h2>
    <p class="text-sm text-slate-600 mb-4">The admin dashboard shows key metrics at a glance:</p>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-slate-200 text-center">
            <i class="fas fa-users text-blue-500 text-lg mb-2"></i>
            <p class="text-xs font-semibold text-slate-900">Total Users</p>
            <p class="text-xs text-slate-400">Active accounts</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200 text-center">
            <i class="fas fa-server text-emerald-500 text-lg mb-2"></i>
            <p class="text-xs font-semibold text-slate-900">Client Systems</p>
            <p class="text-xs text-slate-400">Registered apps</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200 text-center">
            <i class="fas fa-key text-amber-500 text-lg mb-2"></i>
            <p class="text-xs font-semibold text-slate-900">Active Tokens</p>
            <p class="text-xs text-slate-400">Current sessions</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200 text-center">
            <i class="fas fa-clipboard-list text-violet-500 text-lg mb-2"></i>
            <p class="text-xs font-semibold text-slate-900">Audit Events</p>
            <p class="text-xs text-slate-400">Login activity</p>
        </div>
    </div>
    <p class="text-sm text-slate-600">Use the sidebar navigation to access different admin modules. Each section provides full CRUD operations.</p>
</section>

{{-- Client Systems --}}
<section id="clients" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">3. Managing Client Systems</h2>
    <p class="text-sm text-slate-600 mb-4">Client systems represent the applications that use CAS for authentication. Navigate to <strong>Admin → Client Systems</strong>.</p>

    <h3 class="text-sm font-semibold text-slate-900 mt-6 mb-3">Adding a New Client</h3>
    <ol class="space-y-2 text-sm text-slate-600 mb-4">
        <li class="flex items-start gap-2"><span class="font-bold text-slate-900">1.</span> Click "Add New Client System"</li>
        <li class="flex items-start gap-2"><span class="font-bold text-slate-900">2.</span> Fill in the application name and base URL</li>
        <li class="flex items-start gap-2"><span class="font-bold text-slate-900">3.</span> Set the callback URL for SSO redirects</li>
        <li class="flex items-start gap-2"><span class="font-bold text-slate-900">4.</span> Save — credentials are auto-generated</li>
    </ol>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Credential</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Purpose</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Visibility</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr><td class="px-5 py-3 font-mono text-xs">client_id</td><td class="px-5 py-3 text-slate-600">Unique app identifier</td><td class="px-5 py-3 text-slate-600">Always visible</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs">client_secret</td><td class="px-5 py-3 text-slate-600">HMAC signing key</td><td class="px-5 py-3 text-amber-600 font-medium">Shown once only</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs">client_username</td><td class="px-5 py-3 text-slate-600">API authentication</td><td class="px-5 py-3 text-slate-600">Always visible</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs">client_password</td><td class="px-5 py-3 text-slate-600">API authentication</td><td class="px-5 py-3 text-amber-600 font-medium">Shown once only</td></tr>
            </tbody>
        </table>
    </div>

    <h3 class="text-sm font-semibold text-slate-900 mt-6 mb-3">Regenerating Credentials</h3>
    <p class="text-sm text-slate-600">If credentials are compromised, use the <strong>Regenerate Credentials</strong> action. This invalidates all existing tokens for that client and generates new credentials. This is logged in the audit trail.</p>
    <div class="mt-3 bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
            <span class="text-sm text-red-800"><strong>Warning:</strong> Regenerating credentials will break all active sessions for that client application. Update the client's <code class="bg-red-100 px-1 py-0.5 rounded font-mono text-xs">.env</code> immediately.</span>
        </div>
    </div>
</section>

{{-- Users --}}
<section id="users" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">4. User Management</h2>
    <p class="text-sm text-slate-600 mb-4">Navigate to <strong>Admin → Users</strong> to manage all accounts.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div class="p-4 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-2"><i class="fas fa-user-plus text-emerald-500 mr-2"></i>Create Users</h3>
            <p class="text-xs text-slate-500">Add users directly from the admin panel. Set name, email, password, and role assignment.</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-2"><i class="fas fa-user-edit text-blue-500 mr-2"></i>Edit Profiles</h3>
            <p class="text-xs text-slate-500">Update user information, reset passwords, enable/disable 2FA, and change role assignments.</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-2"><i class="fas fa-user-lock text-amber-500 mr-2"></i>Lock / Unlock</h3>
            <p class="text-xs text-slate-500">Manually lock suspicious accounts or unlock accounts that were auto-locked after 5 failed login attempts.</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-2"><i class="fas fa-link text-violet-500 mr-2"></i>Client Links</h3>
            <p class="text-xs text-slate-500">View which client applications each user has authenticated with. Manage user-client associations.</p>
        </div>
    </div>
</section>

{{-- Audit --}}
<section id="audit" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">5. Audit Logs</h2>
    <p class="text-sm text-slate-600 mb-4">Navigate to <strong>Admin → Audit Logs</strong>. Every authentication event is recorded:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Event</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Details Captured</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr><td class="px-5 py-3 font-mono text-xs">login</td><td class="px-5 py-3 text-slate-600">User email, IP, user agent, client system, timestamp</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs">logout</td><td class="px-5 py-3 text-slate-600">Session duration, client system</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs">failed_login</td><td class="px-5 py-3 text-slate-600">Attempted email, IP, failure reason</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs">account_locked</td><td class="px-5 py-3 text-slate-600">User ID, lock duration, failed attempt count</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs">credentials_regenerated</td><td class="px-5 py-3 text-slate-600">Admin user, client system, timestamp</td></tr>
            </tbody>
        </table>
    </div>
    <p class="text-sm text-slate-600">Use the <strong>filter</strong> and <strong>search</strong> tools to narrow results by date range, event type, user, or IP address. Logs can be exported for compliance reporting.</p>
</section>

{{-- IP Whitelist --}}
<section id="ip" class="border-t border-slate-200 pt-10">
    <h2 class="text-xl font-bold text-slate-900 mb-4">6. IP Whitelist</h2>
    <p class="text-sm text-slate-600 mb-4">Navigate to <strong>Admin → IP Whitelist</strong>. Only whitelisted IPs can make API requests to the CAS server.</p>
    <ol class="space-y-2 text-sm text-slate-600 mb-4">
        <li class="flex items-start gap-2"><span class="font-bold text-slate-900">1.</span> Click "Add IP Address"</li>
        <li class="flex items-start gap-2"><span class="font-bold text-slate-900">2.</span> Enter the IP address of your client server</li>
        <li class="flex items-start gap-2"><span class="font-bold text-slate-900">3.</span> Assign it to a specific client system</li>
        <li class="flex items-start gap-2"><span class="font-bold text-slate-900">4.</span> Add a description for reference</li>
    </ol>
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-lightbulb text-blue-500 mt-0.5"></i>
            <span class="text-sm text-blue-800"><strong>Tip:</strong> For development, whitelist <code class="bg-blue-100 px-1 py-0.5 rounded font-mono text-xs">127.0.0.1</code> and <code class="bg-blue-100 px-1 py-0.5 rounded font-mono text-xs">::1</code> (IPv6 localhost). For production, use your server's public IP.</span>
        </div>
    </div>
</section>
@endsection
