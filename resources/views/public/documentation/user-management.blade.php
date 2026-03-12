@extends('public.documentation.layout')

@section('title', 'User Management — CAS SSO')
@section('description', 'How to create, manage, and configure user accounts in CAS SSO.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">How To Use</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">User Management</h1>
        <p class="text-lg text-slate-500 leading-relaxed">Create, manage, and configure user accounts, roles, and permissions.</p>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-slate-200 bg-slate-50/50">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">On This Page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#roles" class="text-blue-600">1. User Roles</a></li>
        <li><a href="#create" class="text-blue-600">2. Creating Users</a></li>
        <li><a href="#api-create" class="text-blue-600">3. Creating Users via API</a></li>
        <li><a href="#profile" class="text-blue-600">4. User Self-Service Portal</a></li>
        <li><a href="#lockout" class="text-blue-600">5. Account Lockout &amp; Recovery</a></li>
    </ol>
</nav>

<section id="roles" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">1. User Roles</h2>
    <p class="text-sm text-slate-600 mb-4">CAS supports two user roles with different access levels:</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center"><i class="fas fa-crown text-violet-600 text-xs"></i></div>
                <h3 class="text-sm font-semibold text-slate-900">Admin</h3>
            </div>
            <ul class="space-y-1.5 text-xs text-slate-600">
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>Access admin dashboard</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>Manage client systems</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>Create / edit / delete users</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>View audit logs</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>Manage IP whitelists</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>Regenerate credentials</li>
            </ul>
        </div>
        <div class="p-5 rounded-xl border border-slate-200">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center"><i class="fas fa-user text-blue-600 text-xs"></i></div>
                <h3 class="text-sm font-semibold text-slate-900">User</h3>
            </div>
            <ul class="space-y-1.5 text-xs text-slate-600">
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>Access user portal</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>View own profile</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>Update personal information</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>Enable / disable 2FA</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>Change password</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>SSO into client apps</li>
            </ul>
        </div>
    </div>
</section>

<section id="create" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">2. Creating Users (Admin Panel)</h2>
    <p class="text-sm text-slate-600 mb-4">Navigate to <strong>Admin → Users → Add User</strong>.</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Field</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Required</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Validation</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr><td class="px-5 py-3 font-medium text-slate-900">Name</td><td class="px-5 py-3"><i class="fas fa-check text-emerald-500"></i></td><td class="px-5 py-3 text-slate-600">2-255 characters</td></tr>
                <tr><td class="px-5 py-3 font-medium text-slate-900">Email</td><td class="px-5 py-3"><i class="fas fa-check text-emerald-500"></i></td><td class="px-5 py-3 text-slate-600">Valid email, unique</td></tr>
                <tr><td class="px-5 py-3 font-medium text-slate-900">Password</td><td class="px-5 py-3"><i class="fas fa-check text-emerald-500"></i></td><td class="px-5 py-3 text-slate-600">Min 8 characters</td></tr>
                <tr><td class="px-5 py-3 font-medium text-slate-900">Role</td><td class="px-5 py-3"><i class="fas fa-check text-emerald-500"></i></td><td class="px-5 py-3 text-slate-600">admin or user</td></tr>
            </tbody>
        </table>
    </div>
</section>

<section id="api-create" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">3. Creating Users via API</h2>
    <p class="text-sm text-slate-600 mb-4">Register users programmatically through the REST API:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
        <div class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="inline-flex items-center px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded">POST</span>
            <span class="text-xs font-medium text-slate-600">/api/register</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300 leading-relaxed"><code>curl -X POST https://cas.muninfosys.com/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secure_password_123",
    "password_confirmation": "secure_password_123"
  }'</code></pre>
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">Success Response</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300 leading-relaxed"><code>{
  "success": true,
  "user": {
    "id": 42,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user"
  }
}</code></pre>
        </div>
    </div>
</section>

<section id="profile" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">4. User Self-Service Portal</h2>
    <p class="text-sm text-slate-600 mb-4">Regular users can manage their own accounts at <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">/user/dashboard</code>:</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-1"><i class="fas fa-id-card text-blue-500 mr-2"></i>Profile</h3>
            <p class="text-xs text-slate-500">Update name, email, and profile picture</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-1"><i class="fas fa-lock text-amber-500 mr-2"></i>Password</h3>
            <p class="text-xs text-slate-500">Change password with current password confirmation</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-1"><i class="fas fa-shield-alt text-emerald-500 mr-2"></i>Two-Factor Auth</h3>
            <p class="text-xs text-slate-500">Enable/disable TOTP-based 2FA with QR code setup</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-1"><i class="fas fa-history text-violet-500 mr-2"></i>Login History</h3>
            <p class="text-xs text-slate-500">View recent login activity, IP addresses, and devices</p>
        </div>
    </div>
</section>

<section id="lockout" class="border-t border-slate-200 pt-10">
    <h2 class="text-xl font-bold text-slate-900 mb-4">5. Account Lockout &amp; Recovery</h2>
    <div class="space-y-3">
        <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200">
            <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-ban text-red-500 text-xs"></i></div>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Auto-Lock</h3>
                <p class="text-xs text-slate-500">After 5 failed login attempts, the account is locked for 30 minutes. The user sees time remaining.</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200">
            <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-unlock text-emerald-500 text-xs"></i></div>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Admin Unlock</h3>
                <p class="text-xs text-slate-500">Admins can manually unlock accounts from <strong>Admin → Users → Unlock</strong>.</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200">
            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-envelope text-blue-500 text-xs"></i></div>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Password Reset</h3>
                <p class="text-xs text-slate-500">Users can reset their password via email at <code class="bg-slate-100 px-1 py-0.5 rounded font-mono text-xs">/auth/forgot-password</code>.</p>
            </div>
        </div>
    </div>
</section>
@endsection
