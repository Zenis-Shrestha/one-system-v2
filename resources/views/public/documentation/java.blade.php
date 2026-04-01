@extends('public.documentation.layout')

@section('title', 'Java Spring Boot Integration — CAS SSO')
@section('description', 'Complete guide for integrating Java Spring Boot applications with CAS Single Sign-On authentication.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fab fa-java text-orange-600 text-lg"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-blue-600 tracking-wide uppercase">Integration Guide</p>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">Java Spring Boot</h1>
            </div>
        </div>
        <p class="text-lg text-slate-500 leading-relaxed mb-4">{{ $javaGuide['description'] }}</p>
        <div class="flex flex-wrap gap-4 text-xs text-slate-500">
            <span><i class="fas fa-clock mr-1"></i>8 min setup</span>
            <span><i class="fas fa-signal mr-1"></i>Intermediate</span>
            <span><i class="fas fa-tag mr-1"></i>Java 17+ / Spring 3</span>
        </div>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-slate-200 bg-slate-50/50">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">On This Page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#dependency" class="text-blue-600 hover:text-blue-800">1. Maven Dependency</a></li>
        <li><a href="#configuration" class="text-blue-600 hover:text-blue-800">2. Configuration</a></li>
        <li><a href="#service" class="text-blue-600 hover:text-blue-800">3. Auth Service</a></li>
        <li><a href="#security" class="text-blue-600 hover:text-blue-800">4. Security Config</a></li>
    </ol>
</nav>

<section id="dependency" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">1. Maven Dependency</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">pom.xml</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code>&lt;<span class="text-red-300">dependency</span>&gt;
    &lt;<span class="text-amber-300">groupId</span>&gt;com.cas-system&lt;/<span class="text-amber-300">groupId</span>&gt;
    &lt;<span class="text-amber-300">artifactId</span>&gt;java-client&lt;/<span class="text-amber-300">artifactId</span>&gt;
    &lt;<span class="text-amber-300">version</span>&gt;2.0.0&lt;/<span class="text-amber-300">version</span>&gt;
&lt;/<span class="text-red-300">dependency</span>&gt;</code></pre>
        </div>
    </div>
</section>

<section id="configuration" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">2. Configuration</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">application.yml</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-amber-300">cas</span>:
  <span class="text-amber-300">server-url</span>: <span class="text-green-400">https://your-cas-server.com</span>
  <span class="text-amber-300">client-id</span>: <span class="text-green-400">your_client_id</span>
  <span class="text-amber-300">client-secret</span>: <span class="text-green-400">your_client_secret</span>
  <span class="text-amber-300">callback-url</span>: <span class="text-green-400">https://your-app.com/cas/callback</span></code></pre>
        </div>
    </div>
</section>

<section id="service" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">3. Auth Service</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">CasAuthService.java</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-violet-400">@Service</span>
<span class="text-violet-400">public class</span> <span class="text-orange-300">CasAuthService</span> {

    <span class="text-violet-400">private final</span> <span class="text-orange-300">RestTemplate</span> restTemplate;
    <span class="text-violet-400">private final</span> <span class="text-orange-300">CasProperties</span> props;

    <span class="text-violet-400">public</span> <span class="text-orange-300">CasUser</span> <span class="text-green-400">validateToken</span>(<span class="text-orange-300">String</span> token) {
        <span class="text-orange-300">Map</span>&lt;String, Object&gt; body = Map.of(
            <span class="text-amber-300">"token"</span>,         token,
            <span class="text-amber-300">"client_id"</span>,     props.getClientId(),
            <span class="text-amber-300">"client_secret"</span>, props.getClientSecret()
        );

        <span class="text-orange-300">ResponseEntity</span>&lt;<span class="text-orange-300">CasResponse</span>&gt; response = restTemplate
            .<span class="text-green-400">postForEntity</span>(
                props.getServerUrl() + <span class="text-amber-300">"/api/sso/validate"</span>,
                body,
                <span class="text-orange-300">CasResponse</span>.<span class="text-blue-400">class</span>
            );

        <span class="text-violet-400">return</span> response.getBody().getUser();
    }
}</code></pre>
        </div>
    </div>
</section>

<section id="security" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">4. Security Config</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">SecurityConfig.java</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-violet-400">@Configuration</span>
<span class="text-violet-400">@EnableWebSecurity</span>
<span class="text-violet-400">public class</span> <span class="text-orange-300">SecurityConfig</span> {

    <span class="text-violet-400">@Bean</span>
    <span class="text-violet-400">public</span> <span class="text-orange-300">SecurityFilterChain</span> <span class="text-green-400">filterChain</span>(<span class="text-orange-300">HttpSecurity</span> http) {
        http
            .<span class="text-green-400">authorizeHttpRequests</span>(auth -> auth
                .requestMatchers(<span class="text-amber-300">"/cas/**"</span>).permitAll()
                .anyRequest().authenticated()
            )
            .<span class="text-green-400">addFilterBefore</span>(
                casTokenFilter(),
                <span class="text-orange-300">UsernamePasswordAuthFilter</span>.<span class="text-blue-400">class</span>
            );
        <span class="text-violet-400">return</span> http.build();
    }
}</code></pre>
        </div>
    </div>

    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="text-sm text-emerald-800"><strong>Done!</strong> Spring Security will intercept requests and validate CAS tokens via the custom filter.</div>
        </div>
    </div>
</section>

<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Next Steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all"><i class="fas fa-code text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">API Reference</span></a>
        <a href="{{ route('docs.security') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all"><i class="fas fa-shield-alt text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Security Guide</span></a>
        <a href="{{ route('docs.deployment') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all"><i class="fas fa-server text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Deployment</span></a>
    </div>
</section>
@endsection