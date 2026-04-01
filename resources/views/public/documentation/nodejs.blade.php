@extends('public.documentation.layout')

@section('title', 'Node.js Integration — CAS SSO')
@section('description', 'Complete guide for integrating Node.js/Express applications with CAS Single Sign-On authentication.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fab fa-node-js text-green-600 text-lg"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-blue-600 tracking-wide uppercase">Integration Guide</p>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">Node.js / Express</h1>
            </div>
        </div>
        <p class="text-lg text-slate-500 leading-relaxed mb-4">{{ $nodejsGuide['description'] }}</p>
        <div class="flex flex-wrap gap-4 text-xs text-slate-500">
            <span><i class="fas fa-clock mr-1"></i>3 min setup</span>
            <span><i class="fas fa-signal mr-1"></i>Easy</span>
            <span><i class="fas fa-tag mr-1"></i>Node 18+</span>
        </div>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-slate-200 bg-slate-50/50">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">On This Page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#installation" class="text-blue-600 hover:text-blue-800">1. Installation</a></li>
        <li><a href="#configuration" class="text-blue-600 hover:text-blue-800">2. Configuration</a></li>
        <li><a href="#middleware" class="text-blue-600 hover:text-blue-800">3. Express Middleware</a></li>
        <li><a href="#routes" class="text-blue-600 hover:text-blue-800">4. Route Protection</a></li>
    </ol>
</nav>

<section id="installation" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">1. Installation</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">Terminal</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>npm install @insol-dev/node-cas-client jsonwebtoken axios</code></pre>
        </div>
    </div>
</section>

<section id="configuration" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">2. Configuration</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">.env</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-amber-300">CAS_SERVER_URL</span>=<span class="text-green-400">https://your-cas-server.com</span>
<span class="text-amber-300">CAS_CLIENT_ID</span>=<span class="text-green-400">your_client_id</span>
<span class="text-amber-300">CAS_CLIENT_SECRET</span>=<span class="text-green-400">your_client_secret</span>
<span class="text-amber-300">CAS_CALLBACK_URL</span>=<span class="text-green-400">https://your-app.com/cas/callback</span></code></pre>
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">config/cas.js</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-violet-400">module.exports</span> = {
  serverUrl:    process.env.<span class="text-amber-300">CAS_SERVER_URL</span>,
  clientId:     process.env.<span class="text-amber-300">CAS_CLIENT_ID</span>,
  clientSecret: process.env.<span class="text-amber-300">CAS_CLIENT_SECRET</span>,
  callbackUrl:  process.env.<span class="text-amber-300">CAS_CALLBACK_URL</span>,
};</code></pre>
        </div>
    </div>
</section>

<section id="middleware" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">3. Express Middleware</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">middleware/cas-auth.js</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-violet-400">const</span> axios = <span class="text-green-400">require</span>(<span class="text-amber-300">'axios'</span>);
<span class="text-violet-400">const</span> config = <span class="text-green-400">require</span>(<span class="text-amber-300">'../config/cas'</span>);

<span class="text-violet-400">async function</span> <span class="text-green-400">casAuth</span>(req, res, next) {
  <span class="text-violet-400">const</span> token = req.headers.authorization?.split(<span class="text-amber-300">' '</span>)[<span class="text-blue-400">1</span>];

  <span class="text-violet-400">if</span> (!token) {
    <span class="text-violet-400">return</span> res.status(<span class="text-blue-400">401</span>).json({ error: <span class="text-amber-300">'Token required'</span> });
  }

  <span class="text-violet-400">try</span> {
    <span class="text-violet-400">const</span> { data } = <span class="text-violet-400">await</span> axios.<span class="text-green-400">post</span>(
      <span class="text-amber-300">`${config.serverUrl}/api/sso/validate`</span>,
      { token, client_id: config.clientId, client_secret: config.clientSecret }
    );

    req.user = data.user;
    <span class="text-green-400">next</span>();
  } <span class="text-violet-400">catch</span> (err) {
    res.status(<span class="text-blue-400">401</span>).json({ error: <span class="text-amber-300">'Invalid token'</span> });
  }
}

<span class="text-violet-400">module.exports</span> = casAuth;</code></pre>
        </div>
    </div>
</section>

<section id="routes" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">4. Route Protection</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">routes/api.js</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-violet-400">const</span> express  = <span class="text-green-400">require</span>(<span class="text-amber-300">'express'</span>);
<span class="text-violet-400">const</span> casAuth  = <span class="text-green-400">require</span>(<span class="text-amber-300">'../middleware/cas-auth'</span>);
<span class="text-violet-400">const</span> router   = express.<span class="text-green-400">Router</span>();

router.<span class="text-green-400">get</span>(<span class="text-amber-300">'/dashboard'</span>, casAuth, (req, res) => {
  res.json({ message: <span class="text-amber-300">'Welcome'</span>, user: req.user });
});

router.<span class="text-green-400">get</span>(<span class="text-amber-300">'/profile'</span>, casAuth, (req, res) => {
  res.json(req.user);
});

<span class="text-violet-400">module.exports</span> = router;</code></pre>
        </div>
    </div>

    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="text-sm text-emerald-800"><strong>Done!</strong> Add <code class="text-xs bg-emerald-100 px-1 py-0.5 rounded font-mono">casAuth</code> middleware to any route that requires SSO authentication.</div>
        </div>
    </div>
</section>

<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Next Steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all"><i class="fas fa-code text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">API Reference</span></a>
        <a href="{{ route('docs.security') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all"><i class="fas fa-shield-alt text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Security Guide</span></a>
        <a href="{{ route('docs.webhooks') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all"><i class="fas fa-bolt text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Webhooks</span></a>
    </div>
</section>
@endsection