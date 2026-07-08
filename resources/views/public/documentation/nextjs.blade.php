@extends('public.documentation.layout')

@section('title', 'Next.js Integration — CAS SSO')
@section('description', 'Complete guide for integrating Next.js App Router applications with CAS Single Sign-On using the @cas-system/nextjs-cas-client SDK.')

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
                <i class="fab fa-react"></i>
            </span>
            <div>
                <p class="os-eyebrow">Integration Guide</p>
                <h1 class="text-3xl font-extrabold text-[var(--color-ink)] tracking-tight leading-tight">Next.js (App Router)</h1>
            </div>
        </div>
        <p class="text-lg text-[var(--color-muted)] leading-relaxed mb-4">{{ $nextjsGuide['description'] }}</p>
        <div class="flex flex-wrap gap-4 text-xs text-[var(--color-muted)]">
            <span><i class="fas fa-clock mr-1"></i>5 min setup</span>
            <span><i class="fas fa-signal mr-1"></i>Easy</span>
            <span><i class="fas fa-tag mr-1"></i>Next 14+ · React 18+ · Node 18.17+</span>
            <span><i class="fas fa-cube mr-1"></i>@cas-system/nextjs-cas-client</span>
        </div>
    </div>
</section>

<nav class="mb-12 os-card os-card-pad">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-3">On This Page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#overview" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">1. Overview</a></li>
        <li><a href="#installation" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">2. Installation</a></li>
        <li><a href="#configuration" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">3. Configuration</a></li>
        <li><a href="#middleware" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">4. Next.js Middleware</a></li>
        <li><a href="#handlers" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">5. API Route Handlers</a></li>
        <li><a href="#server" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">6. Server-Side Auth</a></li>
        <li><a href="#client" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">7. Client Components</a></li>
        <li><a href="#flow" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">8. End-to-End Example</a></li>
    </ol>
</nav>

<section id="overview" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">1. Overview</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        The <code class="os-code-inline">@cas-system/nextjs-cas-client</code>
        SDK wires a Next.js App Router app into the One System CAS server. It ships everything you need: edge middleware to guard
        routes, App Router route handlers for the SSO callback / logout / user endpoints, server-side session helpers, and a client
        provider with hooks and components. The SDK depends only on the built-in <code class="os-code-inline">fetch</code>
        API — no external HTTP or crypto libraries.
    </p>
    <div class="os-card os-card-pad mb-2">
        <div class="os-codeblock-head mb-3"><span>Browser SSO flow</span></div>
        <ol class="space-y-3 text-sm text-[var(--color-ink-2)] list-decimal list-inside">
            <li>A <code class="os-code-inline">CasLoginButton</code> (or the middleware) redirects the browser to <code class="os-code-inline">GET {CAS_SERVER_URL}/sso/login?client_id={CLIENT_ID}</code>.</li>
            <li>After authenticating, the CAS server 302-redirects back to your registered callback with the token appended: <code class="os-code-inline">{callbackUrl}?token={JWT}</code>.</li>
            <li>The callback route handler reads <code class="os-code-inline">token</code> and validates it <strong>server-to-server</strong> via <code class="os-code-inline">POST {CAS_SERVER_URL}/api/validate-token</code> using the <code class="os-code-inline">client_secret</code>. The token is <strong>single-use</strong>.</li>
            <li>On success the handler sets a signed, <code class="os-code-inline">HttpOnly</code> session cookie and redirects to the app.</li>
        </ol>
    </div>
    <div class="os-alert os-alert-warning">
        <i class="fas fa-exclamation-triangle mt-0.5"></i>
        <div><strong>Never ship the client secret to the browser.</strong> Token validation, logout, and token issuance all happen server-side (middleware, route handlers, server helpers). Only <code class="os-code-inline">serverUrl</code> and <code class="os-code-inline">clientId</code> may reach client components.</div>
    </div>
</section>

<section id="installation" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">2. Installation</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">Install from the registry once published, or reference the package from a local path while developing against this repository.</p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>Terminal — from the registry</span></div>
        <pre><code>npm install @cas-system/nextjs-cas-client
<span style="{{ $com }}"># peer deps: next >= 14, react >= 18, react-dom >= 18</span></code></pre>
    </div>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>package.json — from a local path</span></div>
        <pre><code>{
  <span style="{{ $str }}">"dependencies"</span>: {
    <span style="{{ $str }}">"@cas-system/nextjs-cas-client"</span>: <span style="{{ $str }}">"file:../central-authentication-server/packages/nextjs-cas-client"</span>
  }
}</code></pre>
    </div>
</section>

<section id="configuration" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">3. Configuration</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        Set the CAS connection details as environment variables. The server-side modules read these directly; the cookie is signed
        with <code class="os-code-inline">CAS_COOKIE_SECRET</code> (falling back to <code class="os-code-inline">CAS_CLIENT_SECRET</code>).
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>.env.local</span></div>
        <pre><code><span style="{{ $com }}"># Server-only — never exposed to the browser</span>
<span style="{{ $var }}">CAS_SERVER_URL</span>=<span style="{{ $str }}">https://your-cas-server.com</span>
<span style="{{ $var }}">CAS_CLIENT_ID</span>=<span style="{{ $str }}">your_client_id</span>
<span style="{{ $var }}">CAS_CLIENT_SECRET</span>=<span style="{{ $str }}">your_client_secret</span>
<span style="{{ $var }}">CAS_CALLBACK_URL</span>=<span style="{{ $str }}">https://your-app.com/api/cas/callback</span>
<span style="{{ $var }}">CAS_COOKIE_SECRET</span>=<span style="{{ $str }}">a_long_random_string_for_signing_cookies</span>

<span style="{{ $com }}"># Safe to expose for the client-side login redirect</span>
<span style="{{ $var }}">NEXT_PUBLIC_CAS_SERVER_URL</span>=<span style="{{ $str }}">https://your-cas-server.com</span>
<span style="{{ $var }}">NEXT_PUBLIC_CAS_CLIENT_ID</span>=<span style="{{ $str }}">your_client_id</span>
<span style="{{ $var }}">NEXT_PUBLIC_CAS_CALLBACK_URL</span>=<span style="{{ $str }}">https://your-app.com/api/cas/callback</span></code></pre>
    </div>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>lib/cas.ts — shared server config</span></div>
        <pre><code><span style="{{ $kw }}">import</span> <span style="{{ $kw }}">type</span> { CasServerConfig } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/nextjs-cas-client/server'</span>;

<span style="{{ $kw }}">export const</span> casConfig: CasServerConfig = {
  serverUrl:    process.env.<span style="{{ $var }}">CAS_SERVER_URL</span>!,
  clientId:     process.env.<span style="{{ $var }}">CAS_CLIENT_ID</span>!,
  clientSecret: process.env.<span style="{{ $var }}">CAS_CLIENT_SECRET</span>!,
  callbackUrl:  process.env.<span style="{{ $var }}">CAS_CALLBACK_URL</span>,
};</code></pre>
    </div>
</section>

<section id="middleware" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">4. Next.js Middleware</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        <code class="os-code-inline">createCasMiddleware</code> guards the configured
        <code class="os-code-inline">protectedPaths</code>. Unauthenticated requests are redirected to your
        <code class="os-code-inline">loginPath</code> (or straight to the CAS SSO page) with the original URL preserved as <code class="os-code-inline">?returnUrl=…</code>.
    </p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>middleware.ts (project root)</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { createCasMiddleware } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/nextjs-cas-client/middleware'</span>;

<span style="{{ $kw }}">export default</span> <span style="{{ $fn }}">createCasMiddleware</span>({
  protectedPaths: [<span style="{{ $str }}">'/dashboard'</span>, <span style="{{ $str }}">'/admin'</span>, <span style="{{ $str }}">'/settings'</span>],
  publicPaths:    [<span style="{{ $str }}">'/api/health'</span>],
  loginPath:      <span style="{{ $str }}">'/login'</span>, <span style="{{ $com }}">// optional — omit to redirect straight to CAS</span>
});

<span style="{{ $kw }}">export const</span> config = {
  matcher: [<span style="{{ $str }}">'/((?!_next/static|_next/image|favicon.ico).*)'</span>],
};</code></pre>
    </div>
</section>

<section id="handlers" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">5. API Route Handlers</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        Wire up three App Router route handlers. The callback validates the single-use token and creates the session; logout invalidates
        it; the user endpoint hydrates the client provider.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>app/api/cas/callback/route.ts</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { createCallbackHandler } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/nextjs-cas-client/handlers'</span>;
<span style="{{ $kw }}">import</span> { casConfig } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@/lib/cas'</span>;

<span style="{{ $com }}">// Reads ?token=JWT, validates it server-to-server, sets the session cookie,</span>
<span style="{{ $com }}">// then redirects to afterLoginUrl (or the captured ?returnUrl).</span>
<span style="{{ $kw }}">export const</span> GET = <span style="{{ $fn }}">createCallbackHandler</span>({
  cas: casConfig,
  afterLoginUrl: <span style="{{ $str }}">'/dashboard'</span>,
});</code></pre>
    </div>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>app/api/cas/logout/route.ts</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { createLogoutHandler } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/nextjs-cas-client/handlers'</span>;
<span style="{{ $kw }}">import</span> { casConfig } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@/lib/cas'</span>;

<span style="{{ $kw }}">export const</span> POST = <span style="{{ $fn }}">createLogoutHandler</span>({
  cas: casConfig,
  afterLogoutUrl: <span style="{{ $str }}">'/'</span>,
});</code></pre>
    </div>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>app/api/cas/user/route.ts</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { createUserHandler } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/nextjs-cas-client/handlers'</span>;

<span style="{{ $com }}">// Returns { user } from the signed session cookie, or 401 when absent.</span>
<span style="{{ $kw }}">export const</span> GET = <span style="{{ $fn }}">createUserHandler</span>();</code></pre>
    </div>
</section>

<section id="server" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">6. Server-Side Auth</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        Read the session in Server Components with <code class="os-code-inline">getCasSession</code>,
        or protect a route handler with the <code class="os-code-inline">withCasAuth</code> wrapper, which injects the verified
        <code class="os-code-inline">CasUser</code> and returns <code class="os-code-inline">401</code> when there is no valid session.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>app/dashboard/page.tsx — Server Component</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { cookies } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'next/headers'</span>;
<span style="{{ $kw }}">import</span> { redirect } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'next/navigation'</span>;
<span style="{{ $kw }}">import</span> { getCasSession } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/nextjs-cas-client/server'</span>;

<span style="{{ $kw }}">export default async function</span> <span style="{{ $fn }}">DashboardPage</span>() {
  <span style="{{ $kw }}">const</span> session = <span style="{{ $kw }}">await</span> <span style="{{ $fn }}">getCasSession</span>(<span style="{{ $kw }}">await</span> <span style="{{ $fn }}">cookies</span>());
  <span style="{{ $kw }}">if</span> (!session) <span style="{{ $fn }}">redirect</span>(<span style="{{ $str }}">'/login'</span>);

  <span style="{{ $kw }}">return</span> &lt;h1&gt;Welcome {session.user.username}&lt;/h1&gt;;
}</code></pre>
    </div>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>app/api/profile/route.ts — protected handler</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { withCasAuth } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/nextjs-cas-client/server'</span>;

<span style="{{ $kw }}">export const</span> GET = <span style="{{ $fn }}">withCasAuth</span>(<span style="{{ $kw }}">async</span> (req, ctx, user) => {
  <span style="{{ $kw }}">return</span> Response.<span style="{{ $fn }}">json</span>({ message: <span style="{{ $str }}">`Hello ${user.username}`</span>, user });
});</code></pre>
    </div>
</section>

<section id="client" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">7. Client Components</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        Wrap the app in <code class="os-code-inline">CasProvider</code>, then read auth state with the
        <code class="os-code-inline">useCasAuth</code> / <code class="os-code-inline">useCasUser</code> hooks and the
        <code class="os-code-inline">CasLoginButton</code> / <code class="os-code-inline">CasProtectedRoute</code> components.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>app/layout.tsx</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { CasProvider } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/nextjs-cas-client'</span>;

<span style="{{ $kw }}">export default function</span> <span style="{{ $fn }}">RootLayout</span>({ children }: { children: React.ReactNode }) {
  <span style="{{ $kw }}">return</span> (
    &lt;html&gt;
      &lt;body&gt;
        &lt;CasProvider
          casServerUrl={process.env.<span style="{{ $var }}">NEXT_PUBLIC_CAS_SERVER_URL</span>}
          casClientId={process.env.<span style="{{ $var }}">NEXT_PUBLIC_CAS_CLIENT_ID</span>}
          casCallbackUrl={process.env.<span style="{{ $var }}">NEXT_PUBLIC_CAS_CALLBACK_URL</span>}
        &gt;
          {children}
        &lt;/CasProvider&gt;
      &lt;/body&gt;
    &lt;/html&gt;
  );
}</code></pre>
    </div>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>components/Navbar.tsx</span></div>
        <pre><code><span style="{{ $str }}">'use client'</span>;
<span style="{{ $kw }}">import</span> { useCasAuth, CasLoginButton } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/nextjs-cas-client'</span>;

<span style="{{ $kw }}">export function</span> <span style="{{ $fn }}">Navbar</span>() {
  <span style="{{ $kw }}">const</span> { user, isAuthenticated, logout } = <span style="{{ $fn }}">useCasAuth</span>();

  <span style="{{ $kw }}">return</span> isAuthenticated ? (
    &lt;button onClick={logout}&gt;Sign out ({user?.username})&lt;/button&gt;
  ) : (
    &lt;CasLoginButton&gt;Sign in with SSO&lt;/CasLoginButton&gt;
  );
}</code></pre>
    </div>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>components/AdminPanel.tsx — role-gated</span></div>
        <pre><code><span style="{{ $str }}">'use client'</span>;
<span style="{{ $kw }}">import</span> { CasProtectedRoute, useCasUser } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/nextjs-cas-client'</span>;

<span style="{{ $kw }}">export function</span> <span style="{{ $fn }}">AdminPanel</span>() {
  <span style="{{ $kw }}">const</span> { hasRole } = <span style="{{ $fn }}">useCasUser</span>();

  <span style="{{ $kw }}">return</span> (
    &lt;CasProtectedRoute roles={[<span style="{{ $str }}">'admin'</span>]}&gt;
      {hasRole(<span style="{{ $str }}">'admin'</span>) &amp;&amp; &lt;h1&gt;Admin tools&lt;/h1&gt;}
    &lt;/CasProtectedRoute&gt;
  );
}</code></pre>
    </div>
</section>

<section id="flow" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">8. End-to-End Example</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        A minimal login page that kicks off the SSO flow. The button redirects to the CAS server; after authentication the browser lands
        on <code class="os-code-inline">/api/cas/callback?token=…</code>, the handler validates the token server-to-server and sets the
        session cookie, then the user is redirected to <code class="os-code-inline">/dashboard</code>.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>app/login/page.tsx</span></div>
        <pre><code><span style="{{ $str }}">'use client'</span>;
<span style="{{ $kw }}">import</span> { CasLoginButton } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/nextjs-cas-client'</span>;

<span style="{{ $kw }}">export default function</span> <span style="{{ $fn }}">LoginPage</span>() {
  <span style="{{ $kw }}">return</span> (
    &lt;main&gt;
      &lt;h1&gt;Sign in&lt;/h1&gt;
      <span style="{{ $com }}">{/* Redirects to /dashboard after a successful round-trip */}</span>
      &lt;CasLoginButton returnUrl=<span style="{{ $str }}">"/dashboard"</span>&gt;Continue with SSO&lt;/CasLoginButton&gt;
    &lt;/main&gt;
  );
}</code></pre>
    </div>

    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        For machine-to-machine flows, the server-side <code class="os-code-inline">CasClient</code> can issue a token for a known username
        (the CAS issuance endpoint is IP-whitelisted) and validate tokens directly:
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>Server-to-server with CasClient</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { CasClient } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/nextjs-cas-client/server'</span>;
<span style="{{ $kw }}">import</span> { casConfig } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@/lib/cas'</span>;

<span style="{{ $kw }}">const</span> cas = <span style="{{ $kw }}">new</span> <span style="{{ $fn }}">CasClient</span>(casConfig);

<span style="{{ $com }}">// POST {CAS_SERVER_URL}/api/sso/token { client_id, client_secret, username }</span>
<span style="{{ $kw }}">const</span> token = <span style="{{ $kw }}">await</span> cas.<span style="{{ $fn }}">generateToken</span>(<span style="{{ $str }}">'jdoe'</span>);

<span style="{{ $com }}">// POST {CAS_SERVER_URL}/api/validate-token { token, client_id, client_secret }</span>
<span style="{{ $kw }}">const</span> user = <span style="{{ $kw }}">await</span> cas.<span style="{{ $fn }}">validateToken</span>(token!);
<span style="{{ $kw }}">if</span> (user &amp;&amp; CasClient.<span style="{{ $fn }}">hasAnyRole</span>(user, [<span style="{{ $str }}">'admin'</span>, <span style="{{ $str }}">'editor'</span>])) {
  <span style="{{ $com }}">// authorised</span>
}</code></pre>
    </div>

    <div class="os-alert os-alert-success">
        <i class="fas fa-check-circle mt-0.5"></i>
        <div><strong>Done!</strong> The middleware now guards your protected paths, the callback creates a signed session on the single-use token, and client components read the user via <code class="os-code-inline">useCasAuth</code>. Serve over HTTPS in production so the <code class="os-code-inline">Secure</code> session cookie is honoured.</div>
    </div>
</section>

<section class="border-t border-[var(--color-line)] pt-10">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-4">Next Steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="os-card os-card-hover group flex items-center gap-3 p-4"><i class="fas fa-code text-[var(--color-muted)] group-hover:text-[var(--color-accent)] text-sm"></i><span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">API Reference</span></a>
        <a href="{{ route('docs.security') }}" class="os-card os-card-hover group flex items-center gap-3 p-4"><i class="fas fa-shield-alt text-[var(--color-muted)] group-hover:text-[var(--color-accent)] text-sm"></i><span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">Security Guide</span></a>
        <a href="{{ route('docs.sdks') }}" class="os-card os-card-hover group flex items-center gap-3 p-4"><i class="fas fa-cube text-[var(--color-muted)] group-hover:text-[var(--color-accent)] text-sm"></i><span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">SDKs &amp; Packages</span></a>
    </div>
</section>
@endsection
