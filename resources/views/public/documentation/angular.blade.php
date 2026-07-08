@extends('public.documentation.layout')

@section('title', 'Angular Integration — CAS SSO')
@section('description', 'Complete guide for integrating Angular applications with CAS Single Sign-On using guards, HTTP interceptors and reactive services.')

@php
    // Highlight palette tuned to the ONE codeblock surface (dark ink background).
    $kw  = 'color:#c7d2fe';        // keywords / accent-tinted
    $str = 'color:#a5b4fc';        // strings
    $fn  = 'color:#e2e8f0';        // functions / identifiers
    $var = 'color:#cbd5e1';        // variables
    $com = 'color:#64748b';        // comments
    $num = 'color:#93c5fd';        // numbers / literals
@endphp

@section('content')
<section class="border-b border-[var(--color-line)] pb-10 mb-12">
    <div class="">
        <div class="flex items-center gap-3 mb-4">
            <span class="os-icon-tile os-icon-tile-ink">
                <i class="fab fa-angular text-lg"></i>
            </span>
            <div>
                <p class="text-sm font-medium text-[var(--color-accent)] tracking-wide uppercase">Integration Guide</p>
                <h1 class="text-3xl font-extrabold text-[var(--color-ink)] tracking-tight leading-tight">Angular</h1>
            </div>
        </div>
        <p class="text-lg text-[var(--color-muted)] leading-relaxed mb-4">{{ $angularGuide['description'] }}</p>
        <div class="flex flex-wrap gap-4 text-xs text-[var(--color-muted)]">
            <span><i class="fas fa-clock mr-1"></i>5 min setup</span>
            <span><i class="fas fa-signal mr-1"></i>Easy</span>
            <span><i class="fas fa-tag mr-1"></i>Angular 18+</span>
            <span><i class="fas fa-cube mr-1"></i>@cas-system/angular-cas-client</span>
        </div>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-[var(--color-line)] bg-[var(--color-surface-2)]">
    <h2 class="text-xs font-semibold text-[var(--color-muted)] uppercase tracking-widest mb-3">On This Page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#overview" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">1. Overview</a></li>
        <li><a href="#installation" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">2. Installation</a></li>
        <li><a href="#configuration" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">3. Configuration</a></li>
        <li><a href="#module" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">4. Module setup</a></li>
        <li><a href="#callback" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">5. Callback &amp; backend validation</a></li>
        <li><a href="#guards" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">6. Route guards</a></li>
        <li><a href="#interceptors" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">7. HTTP interceptor</a></li>
        <li><a href="#services" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">8. Auth service in components</a></li>
        <li><a href="#example" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">9. End-to-end example</a></li>
    </ol>
</nav>

{{-- 1. OVERVIEW --}}
<section id="overview" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">1. Overview</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        The <code class="os-code-inline">&commat;one-system/angular-cas-client</code> SDK
        wires an Angular app into the CAS Single Sign-On flow with a route guard, an HTTP interceptor and reactive
        services. The browser never holds the client secret — it only kicks off the login redirect and forwards the
        returned token to <strong>your backend</strong> for validation.
    </p>
    <div class="rounded-xl border border-[var(--color-line)] bg-[var(--color-surface-2)] p-5 mb-4">
        <h3 class="text-xs font-semibold text-[var(--color-muted)] uppercase tracking-widest mb-3">Authentication flow</h3>
        <ol class="space-y-2 text-sm text-[var(--color-ink-2)] list-decimal list-inside">
            <li><code class="os-code-inline">CasAuthService.login()</code> redirects the browser to <code class="os-code-inline">{CAS_BASE}/sso/login?client_id=…</code>.</li>
            <li>CAS authenticates the user and redirects back to your registered callback URL with <code class="os-code-inline">?token=JWT</code> appended.</li>
            <li><code class="os-code-inline">CasCallbackComponent</code> extracts the token and POSTs it to your backend.</li>
            <li>Your backend validates <strong>server-to-server</strong> with <code class="os-code-inline">POST {CAS_BASE}/api/validate-token</code> using the <code class="os-code-inline">client_secret</code>.</li>
            <li>The validated user is stored in <code class="os-code-inline">sessionStorage</code> and exposed through reactive observables.</li>
        </ol>
    </div>
    <div class="os-alert os-alert-warning">
        <i class="fas fa-exclamation-triangle mt-0.5"></i>
        <div><strong>Never validate tokens in the browser.</strong> The <code class="os-code-inline">client_secret</code> must stay on your server. The token is single-use — validate it once, then create your own app session.</div>
    </div>
</section>

{{-- 2. INSTALLATION --}}
<section id="installation" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">2. Installation</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">Install from npm:</p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>Terminal</span></div>
        <pre><code>npm install @cas-system/angular-cas-client</code></pre>
    </div>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">Or, to develop against the package straight from the monorepo, reference it by <strong>local path</strong> in your <code class="os-code-inline">package.json</code> and install:</p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>package.json</span></div>
        <pre><code>{
  <span style="{{ $var }}">"dependencies"</span>: {
    <span style="{{ $var }}">"@cas-system/angular-cas-client"</span>: <span style="{{ $str }}">"file:../packages/angular-cas-client"</span>
  }
}</code></pre>
    </div>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>Terminal</span></div>
        <pre><code>npm install</code></pre>
    </div>
    <p class="text-xs text-[var(--color-muted)] mt-3">Peer dependencies: <code class="os-code-inline">@angular/core</code>, <code class="os-code-inline">@angular/common</code>, <code class="os-code-inline">@angular/router</code> (all ≥ 18) and <code class="os-code-inline">rxjs</code> ≥ 7.</p>
</section>

{{-- 3. CONFIGURATION --}}
<section id="configuration" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">3. Configuration</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        The SDK is configured with a <code class="os-code-inline">CasConfig</code> object.
        Keep environment-specific values in Angular's <code class="os-code-inline">environment.ts</code> files.
        Note there is <strong>no client secret here</strong> — only the public <code class="os-code-inline">clientId</code>.
        The secret lives on the backend that <code class="os-code-inline">backendValidateUrl</code> points to.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>src/environments/environment.ts</span></div>
        <pre><code><span style="{{ $kw }}">export const</span> environment = {
  production: <span style="{{ $num }}">false</span>,
  cas: {
    serverUrl:          <span style="{{ $str }}">'https://cas.example.com'</span>,   <span style="{{ $com }}">// CAS_BASE</span>
    clientId:           <span style="{{ $str }}">'my-app-client-id'</span>,          <span style="{{ $com }}">// registered client_id</span>
    callbackUrl:        <span style="{{ $str }}">'https://my-app.com/cas/callback'</span>, <span style="{{ $com }}">// registered callback_url</span>
    backendValidateUrl: <span style="{{ $str }}">'/api/auth/validate'</span>,          <span style="{{ $com }}">// your server endpoint</span>
  },
};</code></pre>
    </div>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>CasConfig (interface)</span></div>
        <pre><code><span style="{{ $kw }}">interface</span> <span style="{{ $fn }}">CasConfig</span> {
  serverUrl:           <span style="{{ $kw }}">string</span>;    <span style="{{ $com }}">// CAS server base URL (no trailing slash)</span>
  clientId:            <span style="{{ $kw }}">string</span>;    <span style="{{ $com }}">// SSO client identifier</span>
  callbackUrl?:        <span style="{{ $kw }}">string</span>;    <span style="{{ $com }}">// default: origin + '/cas/callback'</span>
  backendValidateUrl?: <span style="{{ $kw }}">string</span>;    <span style="{{ $com }}">// your backend's validation endpoint</span>
}</code></pre>
    </div>
</section>

{{-- 4. MODULE SETUP --}}
<section id="module" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">4. Module setup</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        Register the SDK with <code class="os-code-inline">CasModule.forRoot(config)</code>.
        This provides <code class="os-code-inline">CAS_CONFIG</code>, the services, and the
        <code class="os-code-inline">CasTokenInterceptor</code>. The optional second argument
        restricts the interceptor to specific URL prefixes.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>app.module.ts</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { NgModule } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@angular/core'</span>;
<span style="{{ $kw }}">import</span> { BrowserModule } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@angular/platform-browser'</span>;
<span style="{{ $kw }}">import</span> { HttpClientModule } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@angular/common/http'</span>;
<span style="{{ $kw }}">import</span> { CasModule } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/angular-cas-client'</span>;

<span style="{{ $kw }}">import</span> { environment } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'../environments/environment'</span>;
<span style="{{ $kw }}">import</span> { AppRoutingModule } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'./app-routing.module'</span>;
<span style="{{ $kw }}">import</span> { AppComponent } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'./app.component'</span>;

<span style="{{ $fn }}">&commat;NgModule</span>({
  declarations: [AppComponent],
  imports: [
    BrowserModule,
    HttpClientModule,
    AppRoutingModule,

    <span style="{{ $com }}">// ─── CAS SSO ────────────────────────────────</span>
    CasModule.<span style="{{ $fn }}">forRoot</span>(
      environment.cas,
      [<span style="{{ $str }}">'/api/'</span>],   <span style="{{ $com }}">// only attach Bearer header to these URLs</span>
    ),
  ],
  bootstrap: [AppComponent],
})
<span style="{{ $kw }}">export class</span> <span style="{{ $fn }}">AppModule</span> {}</code></pre>
    </div>

    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">Using standalone bootstrap instead of an <code class="os-code-inline">NgModule</code>? Provide <code class="os-code-inline">CAS_CONFIG</code> and the interceptor directly:</p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>main.ts (standalone)</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { bootstrapApplication } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@angular/platform-browser'</span>;
<span style="{{ $kw }}">import</span> { provideRouter } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@angular/router'</span>;
<span style="{{ $kw }}">import</span> { provideHttpClient, withInterceptorsFromDi, HTTP_INTERCEPTORS } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@angular/common/http'</span>;
<span style="{{ $kw }}">import</span> {
  CAS_CONFIG,
  CasTokenInterceptor,
  CasCallbackComponent,
  CasAuthGuard,
} <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/angular-cas-client'</span>;

<span style="{{ $kw }}">import</span> { AppComponent } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'./app/app.component'</span>;
<span style="{{ $kw }}">import</span> { environment } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'./environments/environment'</span>;

<span style="{{ $fn }}">bootstrapApplication</span>(AppComponent, {
  providers: [
    <span style="{{ $fn }}">provideHttpClient</span>(<span style="{{ $fn }}">withInterceptorsFromDi</span>()),
    <span style="{{ $fn }}">provideRouter</span>([
      { path: <span style="{{ $str }}">'cas/callback'</span>, component: CasCallbackComponent },
      { path: <span style="{{ $str }}">'dashboard'</span>, loadComponent: () =&gt; <span style="{{ $kw }}">import</span>(<span style="{{ $str }}">'./app/dashboard.component'</span>).then(m =&gt; m.DashboardComponent), canActivate: [CasAuthGuard] },
    ]),
    { provide: CAS_CONFIG, useValue: environment.cas },
    { provide: HTTP_INTERCEPTORS, useClass: CasTokenInterceptor, multi: <span style="{{ $num }}">true</span> },
  ],
});</code></pre>
    </div>
</section>

{{-- 5. CALLBACK & BACKEND VALIDATION --}}
<section id="callback" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">5. Callback &amp; backend validation</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        Add the SDK's <code class="os-code-inline">CasCallbackComponent</code> at the path that matches
        your registered <code class="os-code-inline">callbackUrl</code>. It extracts <code class="os-code-inline">?token=…</code>
        from the URL, POSTs it to <code class="os-code-inline">backendValidateUrl</code>, stores the user, and
        navigates to the originally-requested route.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>app-routing.module.ts</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { NgModule } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@angular/core'</span>;
<span style="{{ $kw }}">import</span> { RouterModule, Routes } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@angular/router'</span>;
<span style="{{ $kw }}">import</span> { CasCallbackComponent, CasAuthGuard } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/angular-cas-client'</span>;

<span style="{{ $kw }}">import</span> { DashboardComponent } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'./dashboard/dashboard.component'</span>;
<span style="{{ $kw }}">import</span> { LoginComponent } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'./login/login.component'</span>;

<span style="{{ $kw }}">const</span> routes: Routes = [
  <span style="{{ $com }}">// CAS callback — handles the ?token=JWT redirect from the CAS server</span>
  { path: <span style="{{ $str }}">'cas/callback'</span>, component: CasCallbackComponent },

  <span style="{{ $com }}">// Protected route</span>
  { path: <span style="{{ $str }}">'dashboard'</span>, component: DashboardComponent, canActivate: [CasAuthGuard] },

  <span style="{{ $com }}">// Public</span>
  { path: <span style="{{ $str }}">'login'</span>, component: LoginComponent },
  { path: <span style="{{ $str }}">''</span>, redirectTo: <span style="{{ $str }}">'dashboard'</span>, pathMatch: <span style="{{ $str }}">'full'</span> },
];

<span style="{{ $fn }}">&commat;NgModule</span>({ imports: [RouterModule.<span style="{{ $fn }}">forRoot</span>(routes)], exports: [RouterModule] })
<span style="{{ $kw }}">export class</span> <span style="{{ $fn }}">AppRoutingModule</span> {}</code></pre>
    </div>

    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        The browser sends <code class="os-code-inline">{ token, client_id }</code> to your
        <code class="os-code-inline">backendValidateUrl</code>. That endpoint performs the
        <strong>server-to-server</strong> validation against CAS, where the <code class="os-code-inline">client_secret</code> is safe:
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>Your backend — POST /api/auth/validate (Node/Express example)</span></div>
        <pre><code>app.<span style="{{ $fn }}">post</span>(<span style="{{ $str }}">'/api/auth/validate'</span>, <span style="{{ $kw }}">async</span> (req, res) =&gt; {
  <span style="{{ $kw }}">const</span> { token, client_id } = req.body;

  <span style="{{ $kw }}">const</span> { data } = <span style="{{ $kw }}">await</span> axios.<span style="{{ $fn }}">post</span>(
    <span style="{{ $str }}">`${process.env.CAS_BASE}/api/validate-token`</span>,
    {
      token,
      client_id,
      client_secret: process.env.<span style="{{ $var }}">CAS_CLIENT_SECRET</span>,  <span style="{{ $com }}">// never sent to the browser</span>
    },
  );

  <span style="{{ $com }}">// CAS replies: { valid: true, user: { id, username, email }, expires_at }</span>
  <span style="{{ $kw }}">if</span> (!data.valid) <span style="{{ $kw }}">return</span> res.status(<span style="{{ $num }}">401</span>).json({ valid: <span style="{{ $num }}">false</span> });

  <span style="{{ $com }}">// (optionally) create your own app session here, then return the envelope</span>
  res.json(data);
});</code></pre>
    </div>
    <div class="os-alert">
        <i class="fas fa-info-circle mt-0.5"></i>
        <div>The SDK accepts either the CAS envelope <code class="os-code-inline">{ valid, user, expires_at }</code> or a bare <code class="os-code-inline">CasUser</code> returned by your backend. On success it persists the token and user in <code class="os-code-inline">sessionStorage</code>.</div>
    </div>
</section>

{{-- 6. ROUTE GUARDS --}}
<section id="guards" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">6. Route guards</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        <code class="os-code-inline">CasAuthGuard</code> protects routes. If the user is not
        authenticated it triggers the CAS login, returning them to the original URL afterwards. Add
        <code class="os-code-inline">data.roles</code> to require specific roles.
    </p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>Role-based routes</span></div>
        <pre><code><span style="{{ $kw }}">const</span> routes: Routes = [
  <span style="{{ $com }}">// any authenticated user</span>
  { path: <span style="{{ $str }}">'profile'</span>, component: ProfileComponent, canActivate: [CasAuthGuard] },

  <span style="{{ $com }}">// must have the 'admin' role</span>
  { path: <span style="{{ $str }}">'admin'</span>, component: AdminComponent, canActivate: [CasAuthGuard], data: { roles: [<span style="{{ $str }}">'admin'</span>] } },

  <span style="{{ $com }}">// any of these roles</span>
  { path: <span style="{{ $str }}">'reports'</span>, component: ReportsComponent, canActivate: [CasAuthGuard], data: { roles: [<span style="{{ $str }}">'admin'</span>, <span style="{{ $str }}">'analyst'</span>] } },
];</code></pre>
    </div>
</section>

{{-- 7. HTTP INTERCEPTOR --}}
<section id="interceptors" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">7. HTTP interceptor</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        <code class="os-code-inline">CasTokenInterceptor</code> is registered automatically by
        <code class="os-code-inline">CasModule.forRoot()</code> and attaches
        <code class="os-code-inline">Authorization: Bearer &lt;token&gt;</code> to outgoing requests.
        Limit it to specific origins either via the second <code class="os-code-inline">forRoot()</code> argument (shown above)
        or by providing the <code class="os-code-inline">CAS_INTERCEPT_URLS</code> token:
    </p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>Restrict the interceptor</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { CAS_INTERCEPT_URLS } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/angular-cas-client'</span>;

providers: [
  { provide: CAS_INTERCEPT_URLS, useValue: [<span style="{{ $str }}">'/api/'</span>, <span style="{{ $str }}">'https://backend.example.com'</span>] },
],</code></pre>
    </div>
</section>

{{-- 8. AUTH SERVICE IN COMPONENTS --}}
<section id="services" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">8. Auth service in components</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        <code class="os-code-inline">CasAuthService</code> is the high-level reactive wrapper. Inject it to
        read <code class="os-code-inline">user$</code>, <code class="os-code-inline">isAuthenticated$</code> and
        <code class="os-code-inline">isLoading$</code>, or to call <code class="os-code-inline">login()</code> / <code class="os-code-inline">logout()</code>.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>navbar.component.ts</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { Component } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@angular/core'</span>;
<span style="{{ $kw }}">import</span> { CasAuthService } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/angular-cas-client'</span>;

<span style="{{ $fn }}">&commat;Component</span>({
  selector: <span style="{{ $str }}">'app-navbar'</span>,
  template: <span style="{{ $str }}">`
    &lt;nav&gt;
      &lt;ng-container *ngIf="auth.isAuthenticated$ | async; else loggedOut"&gt;
        &lt;span&gt;Hello, &#123;&#123; (auth.user$ | async)?.username &#125;&#125;&lt;/span&gt;
        &lt;button (click)="auth.logout('/')"&gt;Logout&lt;/button&gt;
      &lt;/ng-container&gt;
      &lt;ng-template #loggedOut&gt;
        &lt;button (click)="auth.login()"&gt;Login with CAS&lt;/button&gt;
      &lt;/ng-template&gt;
    &lt;/nav&gt;
  `</span>,
})
<span style="{{ $kw }}">export class</span> <span style="{{ $fn }}">NavbarComponent</span> {
  <span style="{{ $kw }}">constructor</span>(<span style="{{ $kw }}">public</span> auth: CasAuthService) {}
}</code></pre>
    </div>

    <div class="os-card overflow-hidden">
        <div class="os-codeblock-head"><span>CasAuthService API</span></div>
        <table class="w-full text-sm">
            <thead class="bg-[var(--color-surface-2)] border-b border-[var(--color-line)]">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-[var(--color-ink-2)]">Member</th>
                    <th class="text-left px-5 py-3 font-semibold text-[var(--color-ink-2)]">Type</th>
                    <th class="text-left px-5 py-3 font-semibold text-[var(--color-ink-2)]">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--color-line)]">
                <tr><td class="px-5 py-3 font-mono text-xs text-[var(--color-ink)]">user$</td><td class="px-5 py-3 text-[var(--color-muted)] text-xs font-mono">Observable&lt;CasUser|null&gt;</td><td class="px-5 py-3 text-[var(--color-ink-2)]">Reactive stream of the current user</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs text-[var(--color-ink)]">isAuthenticated$</td><td class="px-5 py-3 text-[var(--color-muted)] text-xs font-mono">Observable&lt;boolean&gt;</td><td class="px-5 py-3 text-[var(--color-ink-2)]">True while a session exists</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs text-[var(--color-ink)]">isLoading$</td><td class="px-5 py-3 text-[var(--color-muted)] text-xs font-mono">Observable&lt;boolean&gt;</td><td class="px-5 py-3 text-[var(--color-ink-2)]">True during async auth operations</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs text-[var(--color-ink)]">login(returnUrl?)</td><td class="px-5 py-3 text-[var(--color-muted)] text-xs font-mono">void</td><td class="px-5 py-3 text-[var(--color-ink-2)]">Redirect to the CAS login page</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs text-[var(--color-ink)]">logout(redirectUrl?)</td><td class="px-5 py-3 text-[var(--color-muted)] text-xs font-mono">void</td><td class="px-5 py-3 text-[var(--color-ink-2)]">Clear session and redirect to CAS logout</td></tr>
                <tr><td class="px-5 py-3 font-mono text-xs text-[var(--color-ink)]">handleCallback()</td><td class="px-5 py-3 text-[var(--color-muted)] text-xs font-mono">Observable&lt;CasUser|null&gt;</td><td class="px-5 py-3 text-[var(--color-ink-2)]">Process the callback and update state</td></tr>
            </tbody>
        </table>
    </div>
    <p class="text-xs text-[var(--color-muted)] mt-3">
        Need lower-level helpers? <code class="os-code-inline">CasClientService</code> exposes
        <code class="os-code-inline">getToken()</code>, <code class="os-code-inline">getUser()</code>,
        <code class="os-code-inline">userHasRole()</code>, <code class="os-code-inline">userHasAnyRole()</code> and
        <code class="os-code-inline">userHasAllRoles()</code>.
    </p>
</section>

{{-- 9. END-TO-END EXAMPLE --}}
<section id="example" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">9. End-to-end example</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">A protected dashboard that displays the authenticated user and a role-gated admin link:</p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>dashboard.component.ts</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { Component } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@angular/core'</span>;
<span style="{{ $kw }}">import</span> { CasAuthService, CasClientService } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/angular-cas-client'</span>;

<span style="{{ $fn }}">&commat;Component</span>({
  selector: <span style="{{ $str }}">'app-dashboard'</span>,
  template: <span style="{{ $str }}">`
    &lt;div *ngIf="auth.isLoading$ | async"&gt;Loading…&lt;/div&gt;

    &lt;ng-container *ngIf="auth.user$ | async as user"&gt;
      &lt;h1&gt;Welcome, &#123;&#123; user.username &#125;&#125;&lt;/h1&gt;
      &lt;p&gt;&#123;&#123; user.email &#125;&#125;&lt;/p&gt;

      &lt;a routerLink="/admin" *ngIf="isAdmin"&gt;Admin panel&lt;/a&gt;
      &lt;button (click)="auth.logout('/')"&gt;Sign out&lt;/button&gt;
    &lt;/ng-container&gt;
  `</span>,
})
<span style="{{ $kw }}">export class</span> <span style="{{ $fn }}">DashboardComponent</span> {
  <span style="{{ $kw }}">get</span> <span style="{{ $fn }}">isAdmin</span>(): <span style="{{ $kw }}">boolean</span> {
    <span style="{{ $kw }}">return</span> <span style="{{ $kw }}">this</span>.cas.<span style="{{ $fn }}">userHasRole</span>(<span style="{{ $str }}">'admin'</span>);
  }

  <span style="{{ $kw }}">constructor</span>(
    <span style="{{ $kw }}">public</span> auth: CasAuthService,
    <span style="{{ $kw }}">private</span> cas: CasClientService,
  ) {}
}</code></pre>
    </div>

    <div class="os-alert os-alert-success">
        <i class="fas fa-check-circle mt-0.5"></i>
        <div><strong>Done!</strong> Clicking <em>Login with CAS</em> redirects to the CAS server, the callback validates the token through your backend, and any route guarded by <code class="os-code-inline">CasAuthGuard</code> is now protected. Serve over HTTPS in production.</div>
    </div>
</section>

{{-- NEXT STEPS --}}
<section class="border-t border-[var(--color-line)] pt-10">
    <h2 class="text-xs font-semibold text-[var(--color-muted)] uppercase tracking-widest mb-4">Next Steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="os-card os-card-hover group flex items-center gap-3 p-4"><i class="fas fa-code text-[var(--color-muted)] group-hover:text-[var(--color-accent)] text-sm"></i><span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">API Reference</span></a>
        <a href="{{ route('docs.security') }}" class="os-card os-card-hover group flex items-center gap-3 p-4"><i class="fas fa-shield-alt text-[var(--color-muted)] group-hover:text-[var(--color-accent)] text-sm"></i><span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">Security Guide</span></a>
        <a href="{{ route('docs.sdks') }}" class="os-card os-card-hover group flex items-center gap-3 p-4"><i class="fas fa-cube text-[var(--color-muted)] group-hover:text-[var(--color-accent)] text-sm"></i><span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">All SDKs</span></a>
    </div>
</section>
@endsection
