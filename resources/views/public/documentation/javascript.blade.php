@extends('public.documentation.layout')

@section('title', 'JavaScript Integration — CAS SSO')
@section('description', 'Frontend integration guide for JavaScript SPAs with CAS SSO.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center"><i class="fab fa-js text-yellow-600 text-lg"></i></div>
            <div>
                <p class="text-sm font-medium text-blue-600 tracking-wide uppercase">Integration Guide</p>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">JavaScript / HTML</h1>
            </div>
        </div>
        <p class="text-lg text-slate-500 leading-relaxed mb-4">{{ $javascriptGuide['description'] }}</p>
        <div class="flex flex-wrap gap-4 text-xs text-slate-500">
            <span><i class="fas fa-clock mr-1"></i>5 min</span>
            <span><i class="fas fa-signal mr-1"></i>Easy</span>
            <span><i class="fas fa-tag mr-1"></i>ES2020+</span>
        </div>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-slate-200 bg-slate-50/50">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">On This Page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#sso-redirect" class="text-blue-600">1. SSO Redirect Flow</a></li>
        <li><a href="#callback" class="text-blue-600">2. Handle Callback</a></li>
        <li><a href="#token" class="text-blue-600">3. Token Management</a></li>
        <li><a href="#spa" class="text-blue-600">4. SPA Integration</a></li>
    </ol>
</nav>

<section id="sso-redirect" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">1. SSO Redirect Flow</h2>
    <p class="text-sm text-slate-600 mb-4">For browser-based apps, redirect users to the CAS login page. After authentication, they are returned to your callback URL with a token.</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">auth.js</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300"><code><span class="text-violet-400">const</span> CAS_URL = <span class="text-amber-300">'https://your-cas-server.com'</span>;
<span class="text-violet-400">const</span> CLIENT_ID = <span class="text-amber-300">'your_client_id'</span>;

<span class="text-slate-500">// Redirect user to CAS login page</span>
<span class="text-violet-400">function</span> <span class="text-green-400">loginWithSSO</span>() {
  window.location.href = <span class="text-amber-300">`${CAS_URL}/sso/login?client_id=${CLIENT_ID}`</span>;
}</code></pre>
        </div>
    </div>
</section>

<section id="callback" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">2. Handle Callback</h2>
    <p class="text-sm text-slate-600 mb-4">After login, the user is redirected to your callback URL with a <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">token</code> query parameter. Validate it on your backend.</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">callback.js</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300"><code><span class="text-slate-500">// On your callback page (e.g. /cas/callback)</span>
<span class="text-violet-400">const</span> params = <span class="text-violet-400">new</span> URLSearchParams(window.location.search);
<span class="text-violet-400">const</span> token = params.get(<span class="text-amber-300">'token'</span>);

<span class="text-violet-400">if</span> (token) {
  <span class="text-slate-500">// Send token to YOUR backend for validation</span>
  <span class="text-violet-400">const</span> res = <span class="text-violet-400">await</span> fetch(<span class="text-amber-300">'/api/validate-session'</span>, {
    method: <span class="text-amber-300">'POST'</span>,
    headers: { <span class="text-amber-300">'Content-Type'</span>: <span class="text-amber-300">'application/json'</span> },
    body: JSON.stringify({ token })
  });

  <span class="text-violet-400">const</span> data = <span class="text-violet-400">await</span> res.json();
  <span class="text-violet-400">if</span> (data.valid) {
    <span class="text-slate-500">// User authenticated — store session</span>
    localStorage.setItem(<span class="text-amber-300">'user'</span>, JSON.stringify(data.user));
    window.location.href = <span class="text-amber-300">'/dashboard'</span>;
  }
}</code></pre>
        </div>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mt-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
            <div class="text-sm text-amber-800">
                <strong>Important:</strong> Token validation must happen on your <strong>backend server</strong> (not in the browser) because it requires your <code class="text-xs bg-amber-100 px-1 py-0.5 rounded font-mono">client_secret</code>. Your backend calls <code class="text-xs bg-amber-100 px-1 py-0.5 rounded font-mono">POST /api/sso/validate</code> on the CAS server.
            </div>
        </div>
    </div>
</section>

<section id="token" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">3. Token Management</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">api-client.js</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300"><code><span class="text-violet-400">async function</span> <span class="text-green-400">fetchProtected</span>(url) {
  <span class="text-violet-400">const</span> res = <span class="text-violet-400">await</span> fetch(url, { credentials: <span class="text-amber-300">'include'</span> });
  <span class="text-violet-400">if</span> (res.status === <span class="text-blue-400">401</span>) {
    <span class="text-slate-500">// Session expired — redirect to SSO login</span>
    loginWithSSO();
    <span class="text-violet-400">return</span>;
  }
  <span class="text-violet-400">return</span> res.json();
}</code></pre>
        </div>
    </div>
</section>

<section id="spa" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">4. SPA Integration</h2>
    <p class="text-sm text-slate-600 mb-4">For React, Vue, or Angular apps, use a route guard:</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">React example</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300"><code>function ProtectedRoute({ children }) {
  const user = localStorage.getItem('user');
  if (!user) {
    loginWithSSO();
    return null;
  }
  return children;
}

// Usage
&lt;Route path="/dashboard" element={
  &lt;ProtectedRoute&gt;&lt;Dashboard /&gt;&lt;/ProtectedRoute&gt;
} /&gt;</code></pre>
        </div>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <span class="text-sm text-emerald-800"><strong>Done!</strong> Your SPA is now integrated with CAS SSO via the redirect flow.</span>
        </div>
    </div>
</section>

<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Next Steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-all"><i class="fas fa-code text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700">API Reference</span></a>
        <a href="{{ route('docs.security') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-all"><i class="fas fa-shield-alt text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700">Security Guide</span></a>
        <a href="{{ route('docs.webhooks') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-all"><i class="fas fa-bolt text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700">Webhooks</span></a>
    </div>
</section>
@endsection