@extends('public.documentation.layout')

@section('title', 'Code Examples — CAS SSO')
@section('description', 'Ready-to-use code examples for integrating with CAS SSO across multiple platforms.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">Getting Started</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">Code Examples</h1>
        <p class="text-lg text-slate-500 leading-relaxed">Ready-to-use integration examples for every supported platform.</p>
    </div>
</section>

{{-- Platform Links --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Integration Guides</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('docs.laravel') }}" class="group flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-red-200 hover:bg-red-50/30 transition-all">
            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fab fa-laravel text-red-600"></i></div>
            <div><span class="text-sm font-semibold text-slate-900">Laravel</span><p class="text-xs text-slate-500">Composer &middot; 2 min</p></div>
        </a>
        <a href="{{ route('docs.dotnet') }}" class="group flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-blue-200 hover:bg-blue-50/30 transition-all">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fab fa-microsoft text-blue-600"></i></div>
            <div><span class="text-sm font-semibold text-slate-900">.NET MVC</span><p class="text-xs text-slate-500">NuGet &middot; 10 min</p></div>
        </a>
        <a href="{{ route('docs.nodejs') }}" class="group flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-green-200 hover:bg-green-50/30 transition-all">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fab fa-node-js text-green-600"></i></div>
            <div><span class="text-sm font-semibold text-slate-900">Node.js</span><p class="text-xs text-slate-500">npm &middot; 3 min</p></div>
        </a>
        <a href="{{ route('docs.java') }}" class="group flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-orange-200 hover:bg-orange-50/30 transition-all">
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fab fa-java text-orange-600"></i></div>
            <div><span class="text-sm font-semibold text-slate-900">Java Spring</span><p class="text-xs text-slate-500">Maven &middot; 8 min</p></div>
        </a>
        <a href="{{ route('docs.python') }}" class="group flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-indigo-200 hover:bg-indigo-50/30 transition-all">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fab fa-python text-indigo-600"></i></div>
            <div><span class="text-sm font-semibold text-slate-900">Python Django</span><p class="text-xs text-slate-500">pip &middot; 7 min</p></div>
        </a>
        <a href="{{ route('docs.javascript') }}" class="group flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-yellow-200 hover:bg-yellow-50/30 transition-all">
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fab fa-js text-yellow-600"></i></div>
            <div><span class="text-sm font-semibold text-slate-900">JavaScript</span><p class="text-xs text-slate-500">CDN &middot; 5 min</p></div>
        </a>
    </div>
</section>

{{-- Quick Start CLI Example --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Quick Start — cURL</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">Terminal</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-slate-500"># 1. Generate an SSO token</span>
<span class="text-green-400">curl</span> -X POST https://cas.muninfosys.com/api/sso/token \
  -H <span class="text-amber-300">"Content-Type: application/json"</span> \
  -d <span class="text-amber-300">'{
    "email": "john@example.com",
    "password": "SecureP@ss123!",
    "client_id": "your_client_id",
    "client_secret": "your_client_secret"
  }'</span>

<span class="text-slate-500"># 2. Validate the token</span>
<span class="text-green-400">curl</span> -X POST https://cas.muninfosys.com/api/sso/validate \
  -H <span class="text-amber-300">"Content-Type: application/json"</span> \
  -d <span class="text-amber-300">'{"token": "eyJhbGci..."}'</span></code></pre>
        </div>
    </div>
</section>

{{-- JavaScript Fetch --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">JavaScript — Fetch API</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <i class="fab fa-js text-yellow-500 text-sm mr-2"></i>
            <span class="text-xs font-medium text-slate-600">auth.js</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-violet-400">async function</span> <span class="text-green-400">generateToken</span>(email, password) {
  <span class="text-violet-400">const</span> res = <span class="text-violet-400">await</span> <span class="text-green-400">fetch</span>(<span class="text-amber-300">'https://cas.muninfosys.com/api/sso/token'</span>, {
    method: <span class="text-amber-300">'POST'</span>,
    headers: { <span class="text-amber-300">'Content-Type'</span>: <span class="text-amber-300">'application/json'</span> },
    body: <span class="text-green-400">JSON.stringify</span>({ email, password,
      client_id: <span class="text-amber-300">'your_client_id'</span>,
      client_secret: <span class="text-amber-300">'your_client_secret'</span>
    })
  });
  <span class="text-violet-400">return</span> res.<span class="text-green-400">json</span>();
}

<span class="text-violet-400">async function</span> <span class="text-green-400">validateToken</span>(token) {
  <span class="text-violet-400">const</span> res = <span class="text-violet-400">await</span> <span class="text-green-400">fetch</span>(<span class="text-amber-300">'https://cas.muninfosys.com/api/sso/validate'</span>, {
    method: <span class="text-amber-300">'POST'</span>,
    headers: { <span class="text-amber-300">'Content-Type'</span>: <span class="text-amber-300">'application/json'</span> },
    body: <span class="text-green-400">JSON.stringify</span>({ token })
  });
  <span class="text-violet-400">return</span> res.<span class="text-green-400">json</span>();
}</code></pre>
        </div>
    </div>
</section>

{{-- PHP cURL --}}
<section class="mb-12">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">PHP — cURL</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <i class="fab fa-php text-indigo-500 text-sm mr-2"></i>
            <span class="text-xs font-medium text-slate-600">CasAuth.php</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-violet-400">function</span> <span class="text-green-400">generateSSOToken</span>(<span class="text-red-300">$email</span>, <span class="text-red-300">$password</span>) {
    <span class="text-red-300">$ch</span> = <span class="text-green-400">curl_init</span>(<span class="text-amber-300">'https://cas.muninfosys.com/api/sso/token'</span>);
    <span class="text-green-400">curl_setopt_array</span>(<span class="text-red-300">$ch</span>, [
        CURLOPT_RETURNTRANSFER => <span class="text-blue-400">true</span>,
        CURLOPT_POST           => <span class="text-blue-400">true</span>,
        CURLOPT_HTTPHEADER     => [<span class="text-amber-300">'Content-Type: application/json'</span>],
        CURLOPT_POSTFIELDS     => <span class="text-green-400">json_encode</span>([
            <span class="text-amber-300">'email'</span>         => <span class="text-red-300">$email</span>,
            <span class="text-amber-300">'password'</span>      => <span class="text-red-300">$password</span>,
            <span class="text-amber-300">'client_id'</span>     => <span class="text-amber-300">'your_client_id'</span>,
            <span class="text-amber-300">'client_secret'</span> => <span class="text-amber-300">'your_client_secret'</span>,
        ]),
    ]);
    <span class="text-red-300">$response</span> = <span class="text-green-400">curl_exec</span>(<span class="text-red-300">$ch</span>);
    <span class="text-green-400">curl_close</span>(<span class="text-red-300">$ch</span>);
    <span class="text-violet-400">return</span> <span class="text-green-400">json_decode</span>(<span class="text-red-300">$response</span>, <span class="text-blue-400">true</span>);
}</code></pre>
        </div>
    </div>
</section>

{{-- Python --}}
<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Python — Requests</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <i class="fab fa-python text-indigo-500 text-sm mr-2"></i>
            <span class="text-xs font-medium text-slate-600">cas_auth.py</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-violet-400">import</span> requests

<span class="text-violet-400">def</span> <span class="text-green-400">generate_token</span>(email, password):
    response = requests.<span class="text-green-400">post</span>(
        <span class="text-amber-300">'https://cas.muninfosys.com/api/sso/token'</span>,
        json={
            <span class="text-amber-300">'email'</span>: email,
            <span class="text-amber-300">'password'</span>: password,
            <span class="text-amber-300">'client_id'</span>: <span class="text-amber-300">'your_client_id'</span>,
            <span class="text-amber-300">'client_secret'</span>: <span class="text-amber-300">'your_client_secret'</span>,
        }
    )
    <span class="text-violet-400">return</span> response.<span class="text-green-400">json</span>()</code></pre>
        </div>
    </div>
</section>
@endsection