@extends('public.documentation.layout')

@section('title', 'Quick Start Guide — CAS SSO')
@section('description', 'Get CAS SSO running in 5 minutes with this step-by-step quick start guide.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">How To Use</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">Quick Start Guide</h1>
        <p class="text-lg text-slate-500 leading-relaxed">Get CAS Single Sign-On running and integrate your first application in under 10 minutes.</p>
        <div class="flex flex-wrap gap-4 mt-4 text-xs text-slate-500">
            <span class="flex items-center gap-1"><i class="fas fa-clock"></i> 10 minutes</span>
            <span class="flex items-center gap-1"><i class="fas fa-signal"></i> Beginner</span>
            <span class="flex items-center gap-1"><i class="fas fa-check-circle text-emerald-500"></i> No prerequisites</span>
        </div>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-slate-200 bg-slate-50/50">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Steps</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#step1" class="text-blue-600 hover:text-blue-800">1. Install CAS Server</a></li>
        <li><a href="#step2" class="text-blue-600 hover:text-blue-800">2. Configure Environment</a></li>
        <li><a href="#step3" class="text-blue-600 hover:text-blue-800">3. Run Migrations</a></li>
        <li><a href="#step4" class="text-blue-600 hover:text-blue-800">4. Create Admin Account</a></li>
        <li><a href="#step5" class="text-blue-600 hover:text-blue-800">5. Register a Client Application</a></li>
        <li><a href="#step6" class="text-blue-600 hover:text-blue-800">6. Test SSO Authentication</a></li>
    </ol>
</nav>

{{-- Step 1 --}}
<section id="step1" class="mb-12">
    <div class="flex gap-4 mb-4">
        <div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">1</div>
        <h2 class="text-xl font-bold text-slate-900">Install CAS Server</h2>
    </div>
    <p class="text-sm text-slate-600 mb-4">Clone the repository and install dependencies:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">Terminal</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-slate-500"># Clone the repository</span>
git clone https://github.com/your-org/insol-dev.git
cd insol-dev

<span class="text-slate-500"># Install PHP dependencies</span>
composer install

<span class="text-slate-500"># Install frontend dependencies</span>
npm install && npm run build</code></pre>
        </div>
    </div>
</section>

{{-- Step 2 --}}
<section id="step2" class="mb-12">
    <div class="flex gap-4 mb-4">
        <div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">2</div>
        <h2 class="text-xl font-bold text-slate-900">Configure Environment</h2>
    </div>
    <p class="text-sm text-slate-600 mb-4">Copy the environment file and set your configuration:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">Terminal</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>cp .env.example .env
php artisan key:generate</code></pre>
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">.env</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-slate-500"># Database</span>
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cas_system
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

<span class="text-slate-500"># Redis (optional, recommended for sessions)</span>
CACHE_DRIVER=redis
SESSION_DRIVER=redis

<span class="text-slate-500"># reCAPTCHA (optional)</span>
RECAPTCHA_SITE_KEY=your_recaptcha_key
RECAPTCHA_SECRET_KEY=your_recaptcha_secret</code></pre>
        </div>
    </div>
</section>

{{-- Step 3 --}}
<section id="step3" class="mb-12">
    <div class="flex gap-4 mb-4">
        <div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">3</div>
        <h2 class="text-xl font-bold text-slate-900">Run Migrations</h2>
    </div>
    <p class="text-sm text-slate-600 mb-4">Create the database tables and seed initial data:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>php artisan migrate --seed</code></pre>
        </div>
    </div>
    <p class="text-xs text-slate-500 mt-3">This creates: <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">users</code>, <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">client_systems</code>, <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">sso_tokens</code>, <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">audit_logs</code>, <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">ip_whitelists</code>, and more.</p>
</section>

{{-- Step 4 --}}
<section id="step4" class="mb-12">
    <div class="flex gap-4 mb-4">
        <div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">4</div>
        <h2 class="text-xl font-bold text-slate-900">Create Admin Account</h2>
    </div>
    <p class="text-sm text-slate-600 mb-4">Register your first admin user. Start the server first:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>php artisan serve --port=8000</code></pre>
        </div>
    </div>
    <p class="text-sm text-slate-600 mb-3">Then navigate to <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono">http://localhost:8000/auth/register</code> and create your account. The first user registered is automatically assigned the <strong>admin</strong> role.</p>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
            <span class="text-sm text-amber-800"><strong>Important:</strong> Only the first registered user gets admin privileges. Subsequent users are created as regular users.</span>
        </div>
    </div>
</section>

{{-- Step 5 --}}
<section id="step5" class="mb-12">
    <div class="flex gap-4 mb-4">
        <div class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">5</div>
        <h2 class="text-xl font-bold text-slate-900">Register a Client Application</h2>
    </div>
    <p class="text-sm text-slate-600 mb-4">Go to the Admin Panel and register your first client application:</p>
    <ol class="space-y-3 text-sm text-slate-600 mb-6">
        <li class="flex items-start gap-3">
            <span class="w-5 h-5 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">a</span>
            <span>Navigate to <strong>Admin Panel → Client Systems → Add New</strong></span>
        </li>
        <li class="flex items-start gap-3">
            <span class="w-5 h-5 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">b</span>
            <span>Enter your application name, URL, and callback URL</span>
        </li>
        <li class="flex items-start gap-3">
            <span class="w-5 h-5 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">c</span>
            <span>Save — the system auto-generates <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">client_id</code>, <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">client_secret</code>, <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">client_username</code>, and <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">client_password</code></span>
        </li>
        <li class="flex items-start gap-3">
            <span class="w-5 h-5 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">d</span>
            <span><strong>Copy these credentials immediately</strong> — the secret/password are only shown once</span>
        </li>
    </ol>
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
            <span class="text-sm text-blue-800">Add your client IP to the IP whitelist from <strong>Admin → IP Whitelist</strong> so the CAS server accepts your requests.</span>
        </div>
    </div>
</section>

{{-- Step 6 --}}
<section id="step6" class="mb-12">
    <div class="flex gap-4 mb-4">
        <div class="w-8 h-8 bg-emerald-600 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">6</div>
        <h2 class="text-xl font-bold text-slate-900">Test SSO Authentication</h2>
    </div>
    <p class="text-sm text-slate-600 mb-4">Use cURL to test your first SSO token exchange:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
        <div class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="inline-flex items-center px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded">POST</span>
            <span class="text-xs font-medium text-slate-600">/api/sso/token</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>curl -X POST http://localhost:8000/api/sso/token \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "your_password",
    "client_id": "YOUR_CLIENT_ID",
    "client_secret": "YOUR_CLIENT_SECRET"
  }'</code></pre>
        </div>
    </div>
    <p class="text-sm text-slate-600 mb-4">On success, you'll receive a JWT token:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">Response</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIs...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin"
  }
}</code></pre>
        </div>
    </div>
</section>

{{-- What's Next --}}
<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">What's Next?</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('docs.client-registration') }}" class="group p-5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-plus-circle text-blue-500 mb-2"></i>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Register More Clients</h3>
            <p class="text-xs text-slate-500">Add your other applications to the SSO ecosystem.</p>
        </a>
        <a href="{{ route('docs.admin-panel') }}" class="group p-5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-tachometer-alt text-blue-500 mb-2"></i>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Admin Panel Guide</h3>
            <p class="text-xs text-slate-500">Learn to manage users, audit logs, and system settings.</p>
        </a>
        <a href="{{ route('docs.user-management') }}" class="group p-5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-users text-blue-500 mb-2"></i>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">User Management</h3>
            <p class="text-xs text-slate-500">Create, update, and manage user accounts and roles.</p>
        </a>
        <a href="{{ route('docs.two-factor-auth') }}" class="group p-5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-shield-alt text-blue-500 mb-2"></i>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Enable 2FA</h3>
            <p class="text-xs text-slate-500">Set up two-factor authentication for enhanced security.</p>
        </a>
    </div>
</section>
@endsection
