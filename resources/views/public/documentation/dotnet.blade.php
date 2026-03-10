@extends('public.documentation.layout')

@section('title', '.NET MVC Integration — CAS SSO')
@section('description', 'Complete guide for integrating ASP.NET MVC applications with CAS Single Sign-On authentication.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fab fa-microsoft text-blue-600 text-lg"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-blue-600 tracking-wide uppercase">Integration Guide</p>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">.NET MVC C#</h1>
            </div>
        </div>
        <p class="text-lg text-slate-500 leading-relaxed mb-4">{{ $dotnetGuide['description'] }}</p>
        <div class="flex flex-wrap gap-4 text-xs text-slate-500">
            <span><i class="fas fa-clock mr-1"></i>10 min setup</span>
            <span><i class="fas fa-signal mr-1"></i>Intermediate</span>
            <span><i class="fas fa-tag mr-1"></i>.NET 6+</span>
        </div>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-slate-200 bg-slate-50/50">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">On This Page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#installation" class="text-blue-600 hover:text-blue-800">1. NuGet Installation</a></li>
        <li><a href="#configuration" class="text-blue-600 hover:text-blue-800">2. Configuration</a></li>
        <li><a href="#service" class="text-blue-600 hover:text-blue-800">3. CAS Service</a></li>
        <li><a href="#controller" class="text-blue-600 hover:text-blue-800">4. Controller &amp; Middleware</a></li>
    </ol>
</nav>

<section id="installation" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">1. NuGet Installation</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">Package Manager Console</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>dotnet add package CasSystem.Client
dotnet add package Microsoft.AspNetCore.Authentication.JwtBearer</code></pre>
        </div>
    </div>
</section>

<section id="configuration" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">2. Configuration</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">appsettings.json</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>{
  "<span class="text-amber-300">CasSSO</span>": {
    "<span class="text-amber-300">ServerUrl</span>": "<span class="text-green-400">https://cas-server.com</span>",
    "<span class="text-amber-300">ClientId</span>": "<span class="text-green-400">your_client_id</span>",
    "<span class="text-amber-300">ClientSecret</span>": "<span class="text-green-400">your_client_secret</span>",
    "<span class="text-amber-300">CallbackUrl</span>": "<span class="text-green-400">https://your-app.com/cas/callback</span>"
  }
}</code></pre>
        </div>
    </div>
</section>

<section id="service" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">3. CAS Service</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">Services/CasAuthService.cs</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-violet-400">public class</span> <span class="text-orange-300">CasAuthService</span>
{
    <span class="text-violet-400">private readonly</span> <span class="text-orange-300">HttpClient</span> _client;
    <span class="text-violet-400">private readonly</span> <span class="text-orange-300">CasSettings</span> _settings;

    <span class="text-violet-400">public async</span> Task&lt;<span class="text-orange-300">TokenResponse</span>&gt; <span class="text-green-400">ValidateToken</span>(<span class="text-violet-400">string</span> token)
    {
        <span class="text-violet-400">var</span> payload = <span class="text-violet-400">new</span> {
            token,
            client_id     = _settings.ClientId,
            client_secret = _settings.ClientSecret
        };

        <span class="text-violet-400">var</span> response = <span class="text-violet-400">await</span> _client.<span class="text-green-400">PostAsJsonAsync</span>(
            <span class="text-amber-300">$"{_settings.ServerUrl}/api/validate-token"</span>, payload
        );

        <span class="text-violet-400">return await</span> response.Content
            .<span class="text-green-400">ReadFromJsonAsync</span>&lt;<span class="text-orange-300">TokenResponse</span>&gt;();
    }
}</code></pre>
        </div>
    </div>
</section>

<section id="controller" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">4. Controller &amp; Middleware</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">Program.cs</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>builder.Services.<span class="text-green-400">AddSingleton</span>&lt;<span class="text-orange-300">CasAuthService</span>&gt;();
builder.Services.<span class="text-green-400">AddAuthentication</span>(<span class="text-amber-300">"CasSSO"</span>)
    .<span class="text-green-400">AddScheme</span>&lt;<span class="text-orange-300">CasAuthHandler</span>, <span class="text-orange-300">CasAuthOptions</span>&gt;(<span class="text-amber-300">"CasSSO"</span>, <span class="text-blue-400">null</span>);

app.<span class="text-green-400">UseAuthentication</span>();
app.<span class="text-green-400">UseAuthorization</span>();</code></pre>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">Controllers/DashboardController.cs</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>[<span class="text-orange-300">Authorize</span>]
<span class="text-violet-400">public class</span> <span class="text-orange-300">DashboardController</span> : <span class="text-orange-300">Controller</span>
{
    <span class="text-violet-400">public</span> <span class="text-orange-300">IActionResult</span> <span class="text-green-400">Index</span>()
    {
        <span class="text-violet-400">var</span> user = HttpContext.Items[<span class="text-amber-300">"CasUser"</span>];
        <span class="text-violet-400">return</span> <span class="text-green-400">View</span>(user);
    }
}</code></pre>
        </div>
    </div>
</section>

<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Next Steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-code text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">API Reference</span>
        </a>
        <a href="{{ route('docs.security') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-shield-alt text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Security Guide</span>
        </a>
        <a href="{{ route('docs.deployment') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all">
            <i class="fas fa-server text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Deployment</span>
        </a>
    </div>
</section>
@endsection