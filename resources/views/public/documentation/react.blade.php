@extends('public.documentation.layout')

@section('title', 'React integration — CAS SSO')
@section('description', $reactGuide['description'])

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
            <div class="os-icon-tile os-icon-tile-ink">
                <i class="fab fa-react text-lg"></i>
            </div>
            <div>
                <p class="os-eyebrow">Integration guide</p>
                <h1 class="text-3xl font-bold text-[var(--color-ink)] tracking-tight leading-tight">{{ $reactGuide['title'] }}</h1>
            </div>
        </div>
        <p class="text-lg text-[var(--color-muted)] leading-relaxed mb-4">{{ $reactGuide['description'] }}</p>
        <div class="flex flex-wrap gap-2">
            <span class="os-badge"><i class="fas fa-clock"></i>5 min setup</span>
            <span class="os-badge"><i class="fas fa-signal"></i>Easy</span>
            <span class="os-badge"><i class="fab fa-react"></i>React 18.2+</span>
            <span class="os-badge os-badge-accent"><i class="fas fa-box"></i>@cas-system/react-cas-client</span>
        </div>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-[var(--color-line)] bg-[var(--color-surface-2)]">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-3">On this page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#overview" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">1. Overview</a></li>
        <li><a href="#installation" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">2. {{ $reactGuide['sections']['setup'] }}</a></li>
        <li><a href="#configuration" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">3. Configuration</a></li>
        <li><a href="#provider" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">4. {{ $reactGuide['sections']['provider'] }}</a></li>
        <li><a href="#backend" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">5. Backend validation endpoint</a></li>
        <li><a href="#hooks" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">6. {{ $reactGuide['sections']['hooks'] }}</a></li>
        <li><a href="#components" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">7. {{ $reactGuide['sections']['components'] }}</a></li>
        <li><a href="#roles" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">8. {{ $reactGuide['sections']['roles'] }}</a></li>
        <li><a href="#example" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">9. {{ $reactGuide['sections']['examples'] }}</a></li>
    </ol>
</nav>

{{-- 1. Overview --------------------------------------------------------- --}}
<section id="overview" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">1. Overview</h2>
    <p class="text-[var(--color-muted)] leading-relaxed mb-4">
        The <code class="os-code-inline">@cas-system/react-cas-client</code> SDK wires a React 18+ app into the
        One System CAS server using hooks, a context provider, and route-protection components. It manages the
        full SSO redirect flow, extracts the returned token from the callback URL, and keeps the authenticated
        user in <code class="os-code-inline">sessionStorage</code>.
    </p>
    <div class="os-alert os-alert-warning mb-6">
        <i class="fas fa-shield-halved mt-0.5"></i>
        <div>
            <strong>Secrets never reach the browser.</strong> The SDK only ever holds the JWT. Token validation
            is always delegated to <em>your</em> backend, which calls the CAS server with the
            <code class="os-code-inline">client_secret</code>. Never embed the secret in client-side code.
        </div>
    </div>
    <div class="os-card os-card-pad">
        <h3 class="text-sm font-semibold text-[var(--color-ink-2)] mb-3">Authentication flow</h3>
        <ol class="space-y-2 text-sm text-[var(--color-muted)] list-decimal pl-5">
            <li><code class="os-code-inline">login()</code> redirects the browser to <code class="os-code-inline">{CAS_BASE}/sso/login?client_id=&hellip;</code>.</li>
            <li>The user authenticates on the CAS server.</li>
            <li>CAS 302-redirects back to your registered <code class="os-code-inline">callbackUrl</code> with <code class="os-code-inline">?token={JWT}</code> appended.</li>
            <li><code class="os-code-inline">&lt;CasProvider&gt;</code> reads the token and POSTs it to your <code class="os-code-inline">backendValidateUrl</code>.</li>
            <li>Your backend calls <code class="os-code-inline">POST {CAS_BASE}/api/validate-token</code> with <code class="os-code-inline">{ token, client_id, client_secret }</code> and returns the user.</li>
            <li>The SDK stores the user, clears the token from the URL, and updates React state. The token is single-use.</li>
        </ol>
    </div>
</section>

{{-- 2. Installation ----------------------------------------------------- --}}
<section id="installation" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">2. {{ $reactGuide['sections']['setup'] }}</h2>
    <p class="text-[var(--color-muted)] leading-relaxed mb-4">
        Install from the package registry. The SDK has zero runtime dependencies and only peer-depends on
        <code class="os-code-inline">react &gt;= 18.2</code>.
    </p>
    <div class="os-alert mb-6">
        <i class="fas fa-circle-info mt-0.5"></i>
        <div>
            <strong>Compatibility.</strong> Current package <code class="os-code-inline">v1.0.1</code> &mdash; peer
            <code class="os-code-inline">react &gt;= 18.2</code>, built with TypeScript&nbsp;6. The reference sample runs on
            Vite&nbsp;6, React&nbsp;18.3, and Node&nbsp;20.
        </div>
    </div>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>Terminal</span><span>npm / yarn / pnpm</span></div>
        <pre><code><span class="text-[var(--color-faint)]"># npm</span>
npm install @cas-system/react-cas-client

<span class="text-[var(--color-faint)]"># yarn</span>
yarn add @cas-system/react-cas-client

<span class="text-[var(--color-faint)]"># pnpm</span>
pnpm add @cas-system/react-cas-client</code></pre>
    </div>
    <p class="text-[var(--color-muted)] leading-relaxed mb-4">
        Or declare it as a normal dependency in <code class="os-code-inline">package.json</code>:
    </p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>package.json</span><span>registry dependency</span></div>
        <pre><code>{
  <span class="text-[var(--color-accent-line)]">"dependencies"</span>: {
    <span class="text-[var(--color-accent-line)]">"@cas-system/react-cas-client"</span>: <span style="{{ $str }}">"^1.0.1"</span>
  }
}</code></pre>
    </div>
</section>

{{-- 3. Configuration ---------------------------------------------------- --}}
<section id="configuration" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">3. Configuration</h2>
    <p class="text-[var(--color-muted)] leading-relaxed mb-4">
        The frontend only needs public values. Keep <code class="os-code-inline">CAS_CLIENT_SECRET</code> on the
        server &mdash; it is read by your backend validation route, not by the React app.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>.env</span><span>Vite</span></div>
        <pre><code><span class="text-[var(--color-faint)]"># Exposed to the browser (Vite prefixes public vars with VITE_)</span>
<span style="{{ $var }}">VITE_CAS_SERVER_URL</span>=<span style="{{ $str }}">https://your-cas-server.com</span>
<span style="{{ $var }}">VITE_CAS_CLIENT_ID</span>=<span style="{{ $str }}">your_client_id</span>
<span style="{{ $var }}">VITE_CAS_CALLBACK_URL</span>=<span style="{{ $str }}">https://your-app.com/auth/callback</span>

<span class="text-[var(--color-faint)]"># Server-only — NEVER prefixed with VITE_, read by your backend route</span>
<span style="{{ $var }}">CAS_SERVER_URL</span>=<span style="{{ $str }}">https://your-cas-server.com</span>
<span style="{{ $var }}">CAS_CLIENT_ID</span>=<span style="{{ $str }}">your_client_id</span>
<span style="{{ $var }}">CAS_CLIENT_SECRET</span>=<span style="{{ $str }}">your_client_secret</span></code></pre>
    </div>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>src/casConfig.ts</span><span>CasConfig</span></div>
        <pre><code><span style="{{ $kw }}">import type</span> { CasConfig } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/react-cas-client'</span>;

<span style="{{ $kw }}">export const</span> casConfig: CasConfig = {
  serverUrl:          import.meta.env.<span style="{{ $var }}">VITE_CAS_SERVER_URL</span>,   <span class="text-[var(--color-faint)]">// CAS_BASE, no trailing slash</span>
  clientId:           import.meta.env.<span style="{{ $var }}">VITE_CAS_CLIENT_ID</span>,
  callbackUrl:        import.meta.env.<span style="{{ $var }}">VITE_CAS_CALLBACK_URL</span>, <span class="text-[var(--color-faint)]">// must match the registered callback_url</span>
  backendValidateUrl: <span style="{{ $str }}">'/api/auth/validate'</span>,              <span class="text-[var(--color-faint)]">// your server proxies validation</span>
};</code></pre>
    </div>
    <div class="os-card os-card-pad overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-[var(--color-line)] text-[var(--color-faint)] uppercase text-xs tracking-wider">
                    <th class="py-2 pr-4 font-semibold">Property</th>
                    <th class="py-2 pr-4 font-semibold">Required</th>
                    <th class="py-2 font-semibold">Description</th>
                </tr>
            </thead>
            <tbody class="text-[var(--color-muted)]">
                <tr class="border-b border-[var(--color-line)]">
                    <td class="py-2 pr-4"><code class="os-code-inline">serverUrl</code></td>
                    <td class="py-2 pr-4">Yes</td>
                    <td class="py-2">Base URL of the CAS server (no trailing slash).</td>
                </tr>
                <tr class="border-b border-[var(--color-line)]">
                    <td class="py-2 pr-4"><code class="os-code-inline">clientId</code></td>
                    <td class="py-2 pr-4">Yes</td>
                    <td class="py-2">Client ID registered with the CAS server.</td>
                </tr>
                <tr class="border-b border-[var(--color-line)]">
                    <td class="py-2 pr-4"><code class="os-code-inline">callbackUrl</code></td>
                    <td class="py-2 pr-4">No</td>
                    <td class="py-2">URL the CAS server redirects to after login. Defaults to the current page URL.</td>
                </tr>
                <tr>
                    <td class="py-2 pr-4"><code class="os-code-inline">backendValidateUrl</code></td>
                    <td class="py-2 pr-4">No</td>
                    <td class="py-2">Your backend endpoint that proxies token validation (e.g. <code class="os-code-inline">/api/auth/validate</code>).</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

{{-- 4. Provider setup --------------------------------------------------- --}}
<section id="provider" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">4. {{ $reactGuide['sections']['provider'] }}</h2>
    <p class="text-[var(--color-muted)] leading-relaxed mb-4">
        Wrap your app in <code class="os-code-inline">&lt;CasProvider&gt;</code>. On mount it restores any existing
        session from <code class="os-code-inline">sessionStorage</code>, and if the URL carries a
        <code class="os-code-inline">?token=</code> param (the CAS callback) it validates it automatically.
    </p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>src/main.tsx</span><span>React</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { StrictMode } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'react'</span>;
<span style="{{ $kw }}">import</span> { createRoot } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'react-dom/client'</span>;
<span style="{{ $kw }}">import</span> { CasProvider } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/react-cas-client'</span>;
<span style="{{ $kw }}">import</span> { casConfig } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'./casConfig'</span>;
<span style="{{ $kw }}">import</span> App <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'./App'</span>;

createRoot(document.getElementById(<span style="{{ $str }}">'root'</span>)!).render(
  &lt;StrictMode&gt;
    &lt;CasProvider
      config={casConfig}
      onAuthSuccess={(user) =&gt; console.log(<span style="{{ $str }}">'Welcome,'</span>, user.username)}
      onAuthError={(err) =&gt; console.error(<span style="{{ $str }}">'Auth failed:'</span>, err)}
    &gt;
      &lt;App /&gt;
    &lt;/CasProvider&gt;
  &lt;/StrictMode&gt;,
);</code></pre>
    </div>
</section>

{{-- 5. Backend validation ----------------------------------------------- --}}
<section id="backend" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">5. Backend validation endpoint</h2>
    <p class="text-[var(--color-muted)] leading-relaxed mb-4">
        The SDK POSTs <code class="os-code-inline">{ token }</code> to <code class="os-code-inline">backendValidateUrl</code>.
        Your server forwards it to the CAS server with the secret and returns the bare user object. The CAS server
        responds with <code class="os-code-inline">{ valid, user, expires_at }</code> &mdash; return just
        <code class="os-code-inline">user</code>.
    </p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>server/routes/auth.ts</span><span>Express (server-to-server)</span></div>
        <pre><code><span style="{{ $kw }}">import</span> express <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'express'</span>;
<span style="{{ $kw }}">const</span> router = express.Router();

router.post(<span style="{{ $str }}">'/api/auth/validate'</span>, <span style="{{ $kw }}">async</span> (req, res) =&gt; {
  <span style="{{ $kw }}">const</span> { token } = req.body;

  <span class="text-[var(--color-faint)]">// SERVER-TO-SERVER: client_secret never leaves the backend.</span>
  <span class="text-[var(--color-faint)]">// Use server-only env vars here — NOT the VITE_-prefixed (browser) ones.</span>
  <span style="{{ $kw }}">const</span> casRes = <span style="{{ $kw }}">await</span> fetch(<span style="{{ $str }}">`${process.env.CAS_SERVER_URL}/api/validate-token`</span>, {
    method: <span style="{{ $str }}">'POST'</span>,
    headers: { <span style="{{ $str }}">'Content-Type'</span>: <span style="{{ $str }}">'application/json'</span> },
    body: JSON.stringify({
      token,
      client_id:     process.env.<span style="{{ $var }}">CAS_CLIENT_ID</span>,
      client_secret: process.env.<span style="{{ $var }}">CAS_CLIENT_SECRET</span>,
    }),
  });

  <span style="{{ $kw }}">if</span> (!casRes.ok) {
    <span style="{{ $kw }}">return</span> res.status(<span style="{{ $num }}">401</span>).json({ error: <span style="{{ $str }}">'Invalid token'</span> });
  }

  <span class="text-[var(--color-faint)]">// CAS responds { valid, user: { id, username, email }, expires_at }.</span>
  <span class="text-[var(--color-faint)]">// The SDK expects a bare CasUser, so return just `user`.</span>
  <span style="{{ $kw }}">const</span> { user } = <span style="{{ $kw }}">await</span> casRes.json();
  res.json(user);
});

<span style="{{ $kw }}">export default</span> router;</code></pre>
    </div>
</section>

{{-- 6. Hooks ------------------------------------------------------------- --}}
<section id="hooks" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">6. {{ $reactGuide['sections']['hooks'] }}</h2>
    <p class="text-[var(--color-muted)] leading-relaxed mb-4">
        Use <code class="os-code-inline">useCasAuth()</code> for the full state plus actions
        (<code class="os-code-inline">login</code>, <code class="os-code-inline">logout</code>,
        <code class="os-code-inline">hasRole</code>, <code class="os-code-inline">hasAnyRole</code>), or
        <code class="os-code-inline">useCasUser()</code> for read-only user data and role helpers.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>src/components/Header.tsx</span><span>useCasAuth</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { useCasAuth } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/react-cas-client'</span>;

<span style="{{ $kw }}">export function</span> Header() {
  <span style="{{ $kw }}">const</span> { user, isAuthenticated, isLoading, login, logout } = useCasAuth();

  <span style="{{ $kw }}">if</span> (isLoading) <span style="{{ $kw }}">return</span> &lt;p&gt;Loading&hellip;&lt;/p&gt;;

  <span style="{{ $kw }}">if</span> (!isAuthenticated) {
    <span style="{{ $kw }}">return</span> &lt;button onClick={() =&gt; login(<span style="{{ $str }}">'/dashboard'</span>)}&gt;Sign in&lt;/button&gt;;
  }

  <span style="{{ $kw }}">return</span> (
    &lt;div&gt;
      Welcome, {user?.username}!
      &lt;button onClick={() =&gt; logout(<span style="{{ $str }}">'/'</span>)}&gt;Sign out&lt;/button&gt;
    &lt;/div&gt;
  );
}</code></pre>
    </div>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>src/components/ProfileBadge.tsx</span><span>useCasUser</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { useCasUser } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/react-cas-client'</span>;

<span style="{{ $kw }}">export function</span> ProfileBadge() {
  <span style="{{ $kw }}">const</span> { user, hasRole, hasAllRoles } = useCasUser();
  <span style="{{ $kw }}">if</span> (!user) <span style="{{ $kw }}">return null</span>;

  <span style="{{ $kw }}">return</span> (
    &lt;span&gt;
      {user.username}
      {hasRole(<span style="{{ $str }}">'admin'</span>) &amp;&amp; <span style="{{ $str }}">' (admin)'</span>}
    &lt;/span&gt;
  );
}</code></pre>
    </div>
</section>

{{-- 7. Components ------------------------------------------------------- --}}
<section id="components" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">7. {{ $reactGuide['sections']['components'] }}</h2>
    <p class="text-[var(--color-muted)] leading-relaxed mb-4">
        <code class="os-code-inline">&lt;CasProtectedRoute&gt;</code> guards a subtree: unauthenticated users are
        redirected to CAS login, while <code class="os-code-inline">fallback</code> renders during the loading
        state. <code class="os-code-inline">&lt;CasLoginButton&gt;</code> is a pre-wired button that triggers
        <code class="os-code-inline">login()</code> on click.
    </p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>src/AppRoutes.tsx</span><span>react-router-dom</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { Routes, Route } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'react-router-dom'</span>;
<span style="{{ $kw }}">import</span> {
  CasProtectedRoute,
  CasLoginButton,
} <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/react-cas-client'</span>;

<span style="{{ $kw }}">export function</span> AppRoutes() {
  <span style="{{ $kw }}">return</span> (
    &lt;Routes&gt;
      &lt;Route path=<span style="{{ $str }}">"/"</span> element={&lt;Home /&gt;} /&gt;

      <span class="text-[var(--color-faint)]">{/* CasProvider auto-handles the ?token= param here */}</span>
      &lt;Route path=<span style="{{ $str }}">"/auth/callback"</span> element={&lt;p&gt;Authenticating&hellip;&lt;/p&gt;} /&gt;

      &lt;Route
        path=<span style="{{ $str }}">"/dashboard"</span>
        element={
          &lt;CasProtectedRoute fallback={&lt;Spinner /&gt;}&gt;
            &lt;Dashboard /&gt;
          &lt;/CasProtectedRoute&gt;
        }
      /&gt;
    &lt;/Routes&gt;
  );
}

<span class="text-[var(--color-faint)]">// Drop-in sign-in button</span>
&lt;CasLoginButton className=<span style="{{ $str }}">"os-btn os-btn-primary"</span> returnUrl=<span style="{{ $str }}">"/dashboard"</span>&gt;
  Sign in with SSO
&lt;/CasLoginButton&gt;</code></pre>
    </div>
</section>

{{-- 8. Role-based access ------------------------------------------------ --}}
<section id="roles" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">8. {{ $reactGuide['sections']['roles'] }}</h2>
    <p class="text-[var(--color-muted)] leading-relaxed mb-4">
        Pass <code class="os-code-inline">roles</code> to <code class="os-code-inline">&lt;CasProtectedRoute&gt;</code>
        to require at least one of them. Supply an <code class="os-code-inline">unauthorizedComponent</code> to show a
        forbidden view instead of redirecting. The same checks are available as
        <code class="os-code-inline">hasRole</code>, <code class="os-code-inline">hasAnyRole</code>, and
        <code class="os-code-inline">hasAllRoles</code> for conditional UI.
    </p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>src/AdminArea.tsx</span><span>role-based</span></div>
        <pre><code><span style="{{ $kw }}">import</span> {
  CasProtectedRoute,
  useCasUser,
} <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/react-cas-client'</span>;

<span style="{{ $kw }}">function</span> Sidebar() {
  <span style="{{ $kw }}">const</span> { hasRole, hasAnyRole } = useCasUser();
  <span style="{{ $kw }}">return</span> (
    &lt;nav&gt;
      &lt;a href=<span style="{{ $str }}">"/dashboard"</span>&gt;Dashboard&lt;/a&gt;
      {hasAnyRole([<span style="{{ $str }}">'admin'</span>, <span style="{{ $str }}">'manager'</span>]) &amp;&amp; &lt;a href=<span style="{{ $str }}">"/reports"</span>&gt;Reports&lt;/a&gt;}
      {hasRole(<span style="{{ $str }}">'admin'</span>) &amp;&amp; &lt;a href=<span style="{{ $str }}">"/admin"</span>&gt;Admin&lt;/a&gt;}
    &lt;/nav&gt;
  );
}

<span style="{{ $kw }}">function</span> AdminRoute() {
  <span style="{{ $kw }}">return</span> (
    &lt;CasProtectedRoute
      roles={[<span style="{{ $str }}">'admin'</span>]}
      unauthorizedComponent={&lt;Forbidden /&gt;}
      fallback={&lt;Spinner /&gt;}
    &gt;
      &lt;AdminPanel /&gt;
    &lt;/CasProtectedRoute&gt;
  );
}</code></pre>
    </div>
</section>

{{-- 9. End-to-end example ----------------------------------------------- --}}
<section id="example" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">9. {{ $reactGuide['sections']['examples'] }}</h2>
    <p class="text-[var(--color-muted)] leading-relaxed mb-4">
        A complete app: provider at the root, a public home page with a login button, an auto-handled callback
        route, and a protected dashboard. This follows the full CAS protocol &mdash; redirect to
        <code class="os-code-inline">/sso/login</code>, return with a token, validate server-to-server, then
        establish the React session.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>src/App.tsx</span><span>end-to-end</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { BrowserRouter, Routes, Route } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'react-router-dom'</span>;
<span style="{{ $kw }}">import</span> {
  CasProvider,
  CasProtectedRoute,
  CasLoginButton,
  useCasAuth,
} <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/react-cas-client'</span>;
<span style="{{ $kw }}">import</span> { casConfig } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'./casConfig'</span>;

<span style="{{ $kw }}">function</span> Home() {
  <span style="{{ $kw }}">const</span> { isAuthenticated, user } = useCasAuth();
  <span style="{{ $kw }}">return</span> isAuthenticated
    ? &lt;p&gt;Signed in as {user?.username}&lt;/p&gt;
    : &lt;CasLoginButton className=<span style="{{ $str }}">"os-btn os-btn-primary"</span> returnUrl=<span style="{{ $str }}">"/dashboard"</span>&gt;
        Sign in with SSO
      &lt;/CasLoginButton&gt;;
}

<span style="{{ $kw }}">function</span> Dashboard() {
  <span style="{{ $kw }}">const</span> { user, logout } = useCasAuth();
  <span style="{{ $kw }}">return</span> (
    &lt;div&gt;
      &lt;h1&gt;Dashboard&lt;/h1&gt;
      &lt;p&gt;{user?.email}&lt;/p&gt;
      &lt;button onClick={() =&gt; logout(<span style="{{ $str }}">'/'</span>)}&gt;Sign out&lt;/button&gt;
    &lt;/div&gt;
  );
}

<span style="{{ $kw }}">export default function</span> App() {
  <span style="{{ $kw }}">return</span> (
    &lt;BrowserRouter&gt;
      &lt;CasProvider config={casConfig}&gt;
        &lt;Routes&gt;
          &lt;Route path=<span style="{{ $str }}">"/"</span> element={&lt;Home /&gt;} /&gt;
          &lt;Route path=<span style="{{ $str }}">"/auth/callback"</span> element={&lt;p&gt;Authenticating&hellip;&lt;/p&gt;} /&gt;
          &lt;Route
            path=<span style="{{ $str }}">"/dashboard"</span>
            element={
              &lt;CasProtectedRoute fallback={&lt;p&gt;Loading&hellip;&lt;/p&gt;}&gt;
                &lt;Dashboard /&gt;
              &lt;/CasProtectedRoute&gt;
            }
          /&gt;
        &lt;/Routes&gt;
      &lt;/CasProvider&gt;
    &lt;/BrowserRouter&gt;
  );
}</code></pre>
    </div>

    <p class="text-[var(--color-muted)] leading-relaxed mb-4">
        Prefer to drive the flow manually? The low-level <code class="os-code-inline">CasClient</code> class exposes
        every step directly.
    </p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>src/casClient.ts</span><span>CasClient (advanced)</span></div>
        <pre><code><span style="{{ $kw }}">import</span> { CasClient } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'@cas-system/react-cas-client'</span>;
<span style="{{ $kw }}">import</span> { casConfig } <span style="{{ $kw }}">from</span> <span style="{{ $str }}">'./casConfig'</span>;

<span style="{{ $kw }}">const</span> client = <span style="{{ $kw }}">new</span> CasClient(casConfig);

client.login(<span style="{{ $str }}">'/dashboard'</span>);          <span class="text-[var(--color-faint)]">// redirect to {CAS_BASE}/sso/login</span>
<span style="{{ $kw }}">const</span> user = <span style="{{ $kw }}">await</span> client.handleCallback(); <span class="text-[var(--color-faint)]">// extract + validate + store + clean URL</span>
client.isAuthenticated();             <span class="text-[var(--color-faint)]">// boolean</span>
client.userHasRole(<span style="{{ $str }}">'admin'</span>);          <span class="text-[var(--color-faint)]">// role check</span>
<span style="{{ $kw }}">await</span> client.logout(<span style="{{ $str }}">'/'</span>);             <span class="text-[var(--color-faint)]">// clear session + POST {CAS_BASE}/api/logout</span></code></pre>
    </div>

    <div class="os-alert os-alert-success mt-6">
        <i class="fas fa-circle-check mt-0.5"></i>
        <div>
            <strong>Done.</strong> Your React app now signs users in through the CAS server, validates tokens
            server-side, and protects routes by authentication and role.
        </div>
    </div>
</section>

{{-- Next steps ---------------------------------------------------------- --}}
<section class="border-t border-[var(--color-line)] pt-10">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-4">Next steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="os-card os-card-hover group flex items-center gap-3 p-4">
            <i class="fas fa-code text-[var(--color-muted)] text-sm"></i>
            <span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">API reference</span>
        </a>
        <a href="{{ route('docs.security') }}" class="os-card os-card-hover group flex items-center gap-3 p-4">
            <i class="fas fa-shield-halved text-[var(--color-muted)] text-sm"></i>
            <span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">Security guide</span>
        </a>
        <a href="{{ route('docs.sdks') }}" class="os-card os-card-hover group flex items-center gap-3 p-4">
            <i class="fas fa-cube text-[var(--color-muted)] text-sm"></i>
            <span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">SDKs &amp; packages</span>
        </a>
    </div>
</section>
@endsection
