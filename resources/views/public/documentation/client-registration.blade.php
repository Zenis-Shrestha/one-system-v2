@extends('public.documentation.layout')

@section('title', 'Client Registration — CAS SSO')
@section('description', 'Step-by-step guide to register client applications for CAS SSO authentication.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">How To Use</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">Client Registration</h1>
        <p class="text-lg text-slate-500 leading-relaxed">How to register your applications with CAS and configure SSO integration.</p>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-slate-200 bg-slate-50/50">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">On This Page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#what" class="text-blue-600">1. What is a Client System?</a></li>
        <li><a href="#register" class="text-blue-600">2. Registering a Client</a></li>
        <li><a href="#credentials" class="text-blue-600">3. Understanding Credentials</a></li>
        <li><a href="#configure" class="text-blue-600">4. Configuring Your App</a></li>
        <li><a href="#test" class="text-blue-600">5. Testing the Connection</a></li>
    </ol>
</nav>

<section id="what" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">1. What is a Client System?</h2>
    <p class="text-sm text-slate-600 mb-4">A client system is any application that uses CAS for user authentication. Each client gets unique credentials for secure communication with the CAS server.</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl border border-slate-200">
            <i class="fas fa-globe text-blue-500 mb-2"></i>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Web Apps</h3>
            <p class="text-xs text-slate-500">Laravel, Django, Express, Spring Boot, .NET MVC applications</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <i class="fas fa-mobile-alt text-emerald-500 mb-2"></i>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Mobile Apps</h3>
            <p class="text-xs text-slate-500">iOS, Android, React Native, and Flutter applications</p>
        </div>
        <div class="p-4 rounded-xl border border-slate-200">
            <i class="fas fa-desktop text-violet-500 mb-2"></i>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Internal Tools</h3>
            <p class="text-xs text-slate-500">Admin panels, CRM systems, HR portals, internal dashboards</p>
        </div>
    </div>
</section>

<section id="register" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">2. Registering a Client</h2>
    <p class="text-sm text-slate-600 mb-4">Navigate to <strong>Admin Panel → Client Systems → Add New</strong> and fill in:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Field</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Example</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr><td class="px-5 py-3 font-medium text-slate-900">System Name</td><td class="px-5 py-3 font-mono text-xs">Customer Portal</td><td class="px-5 py-3 text-slate-600">Human-readable application name</td></tr>
                <tr><td class="px-5 py-3 font-medium text-slate-900">System URL</td><td class="px-5 py-3 font-mono text-xs">https://portal.company.com</td><td class="px-5 py-3 text-slate-600">Base URL of the client application</td></tr>
                <tr><td class="px-5 py-3 font-medium text-slate-900">Callback URL</td><td class="px-5 py-3 font-mono text-xs">https://portal.company.com/cas/callback</td><td class="px-5 py-3 text-slate-600">Where CAS redirects after login</td></tr>
                <tr><td class="px-5 py-3 font-medium text-slate-900">Status</td><td class="px-5 py-3 font-mono text-xs">Active</td><td class="px-5 py-3 text-slate-600">Enable/disable client</td></tr>
            </tbody>
        </table>
    </div>
</section>

<section id="credentials" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">3. Understanding Credentials</h2>
    <p class="text-sm text-slate-600 mb-4">After saving, the system generates 4 credentials:</p>
    <div class="space-y-3 mb-6">
        <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200">
            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-fingerprint text-slate-600 text-xs"></i></div>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">client_id</h3>
                <p class="text-xs text-slate-500">Unique identifier for your app. Sent with every API request. Visible anytime in the admin panel.</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl border border-amber-200 bg-amber-50/50">
            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-key text-amber-600 text-xs"></i></div>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">client_secret</h3>
                <p class="text-xs text-slate-500">Used to generate HMAC signatures. <strong class="text-amber-700">Shown only once</strong> — copy immediately and store securely.</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200">
            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-user text-slate-600 text-xs"></i></div>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">client_username</h3>
                <p class="text-xs text-slate-500">Username for API authentication. Used alongside client_password for server-to-server calls.</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl border border-amber-200 bg-amber-50/50">
            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-lock text-amber-600 text-xs"></i></div>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">client_password</h3>
                <p class="text-xs text-slate-500">Encrypted password for API calls. <strong class="text-amber-700">Shown only once</strong> — store in your <code class="bg-amber-100 px-1 py-0.5 rounded font-mono text-xs">.env</code> file.</p>
            </div>
        </div>
    </div>
</section>

<section id="configure" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">4. Configuring Your App</h2>
    <p class="text-sm text-slate-600 mb-4">Add the credentials to your client application's environment:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">.env (client application)</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300 leading-relaxed"><code>CAS_SERVER_URL=https://your-cas-server.com
CAS_CLIENT_ID=generated_client_id
CAS_CLIENT_SECRET=generated_client_secret
CAS_CLIENT_USERNAME=generated_username
CAS_CLIENT_PASSWORD=generated_password
CAS_CALLBACK_URL=https://your-app.com/cas/callback</code></pre>
        </div>
    </div>
</section>

<section id="test" class="border-t border-slate-200 pt-10">
    <h2 class="text-xl font-bold text-slate-900 mb-4">5. Testing the Connection</h2>
    <p class="text-sm text-slate-600 mb-4">Verify your client can communicate with CAS:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300 leading-relaxed"><code>curl -X POST https://your-cas-server.com/api/sso/token \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password","client_id":"YOUR_ID","client_secret":"YOUR_SECRET"}'</code></pre>
        </div>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <span class="text-sm text-emerald-800">If you receive a <code class="bg-emerald-100 px-1 py-0.5 rounded font-mono text-xs">success: true</code> response with a JWT token, your client is properly configured.</span>
        </div>
    </div>
</section>
@endsection
