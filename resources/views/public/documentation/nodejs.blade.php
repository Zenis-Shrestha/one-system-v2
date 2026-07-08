@extends('public.documentation.layout')

@section('title', 'Node.js integration — CAS SSO')
@section('description', 'Integrate Node.js and Express applications with CAS Single Sign-On using the official @cas-system/node-cas-client package.')

@section('content')
@php
    // Highlight palette tuned to the ONE codeblock surface (dark ink background).
    $kw  = 'color:#c7d2fe';        // keywords / accent-tinted
    $str = 'color:#a5b4fc';        // strings
    $fn  = 'color:#e2e8f0';        // functions / identifiers
    $var = 'color:#cbd5e1';        // variables
    $com = 'color:#64748b';        // comments
    $num = 'color:#93c5fd';        // numbers / literals
@endphp
<section class="border-b border-[var(--color-line)] pb-10 mb-12">
    <div class="">
        <div class="flex items-center gap-3 mb-4">
            <span class="os-icon-tile os-icon-tile-ink">
                <i class="fab fa-node-js"></i>
            </span>
            <div>
                <p class="os-eyebrow">Integration guide</p>
                <h1 class="text-3xl font-bold text-[var(--color-ink)] tracking-tight leading-tight">Node.js / Express</h1>
            </div>
        </div>
        <p class="text-lg text-[var(--color-muted)] leading-relaxed mb-5">{{ $nodejsGuide['description'] }}</p>
        <div class="flex flex-wrap gap-2">
            <span class="os-badge"><i class="fas fa-clock"></i>3 min setup</span>
            <span class="os-badge"><i class="fas fa-signal"></i>Beginner</span>
            <span class="os-badge"><i class="fas fa-tag"></i>Node 18+</span>
            <span class="os-badge os-badge-accent"><i class="fab fa-npm"></i>v1.0.0</span>
        </div>
    </div>
</section>

<nav class="mb-12 os-card os-card-pad">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-3">On this page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#installation" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">1. Installation</a></li>
        <li><a href="#configuration" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">2. Configuration</a></li>
        <li><a href="#client" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">3. Initialize the client</a></li>
        <li><a href="#login" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">4. Login &amp; callback</a></li>
        <li><a href="#middleware" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">5. Protect routes</a></li>
        <li><a href="#server-to-server" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">6. Server-to-server tokens</a></li>
        <li><a href="#api" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">7. API reference</a></li>
    </ol>
</nav>

<section id="installation" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-2">1. Installation</h2>
    <p class="text-[var(--color-muted)] mb-4">Install the official client from npm. The package ships <code class="os-code-inline">axios</code> as its only dependency, so there is nothing else to add.</p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>Terminal</span></div>
        <pre><code>npm install @cas-system/node-cas-client</code></pre>
    </div>
</section>

<section id="configuration" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-2">2. Configuration</h2>
    <p class="text-[var(--color-muted)] mb-4">Store your CAS credentials in environment variables. <code class="os-code-inline">CAS_SERVER_URL</code> is the CAS server origin and <code class="os-code-inline">CAS_CALLBACK_URL</code> must match the callback registered for your client.</p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>.env</span></div>
        <pre><code><span style="{{ $var }}">CAS_SERVER_URL</span>=<span style="{{ $str }}">https://your-cas-server.com</span>
<span style="{{ $var }}">CAS_CLIENT_ID</span>=<span style="{{ $str }}">your_client_id</span>
<span style="{{ $var }}">CAS_CLIENT_SECRET</span>=<span style="{{ $str }}">your_client_secret</span>
<span style="{{ $var }}">CAS_CALLBACK_URL</span>=<span style="{{ $str }}">https://your-app.com/cas/callback</span></code></pre>
    </div>
    <div class="os-alert mt-4">
        <i class="fas fa-shield-halved mt-0.5 text-[var(--color-muted)]"></i>
        <div>The client secret is used only for server-to-server calls and must never reach the browser. Serve your app over HTTPS in production.</div>
    </div>
</section>

<section id="client" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-2">3. Initialize the client</h2>
    <p class="text-[var(--color-muted)] mb-4">The package exports a single <code class="os-code-inline">CasClient</code> class. Create one instance and reuse it across your app.</p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>cas.js</span></div>
        <pre><code><span style="{{ $kw }}">const</span> CasClient = <span style="{{ $fn }}">require</span>(<span style="{{ $str }}">'@cas-system/node-cas-client'</span>);

<span style="{{ $kw }}">const</span> cas = <span style="{{ $kw }}">new</span> <span style="{{ $fn }}">CasClient</span>({
  serverUrl:    process.env.<span style="{{ $var }}">CAS_SERVER_URL</span>,
  clientId:     process.env.<span style="{{ $var }}">CAS_CLIENT_ID</span>,
  clientSecret: process.env.<span style="{{ $var }}">CAS_CLIENT_SECRET</span>,
  callbackUrl:  process.env.<span style="{{ $var }}">CAS_CALLBACK_URL</span>,
  <span style="{{ $com }}">// Optional</span>
  timeout:      <span style="{{ $num }}">30000</span>,
  verifySsl:    <span style="{{ $num }}">true</span>,
});

<span style="{{ $kw }}">module.exports</span> = cas;</code></pre>
    </div>
</section>

<section id="login" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-2">4. Login &amp; callback</h2>
    <p class="text-[var(--color-muted)] mb-4">Redirect the browser to the CAS login URL. The CAS server authenticates the user and redirects back to your registered callback with a single-use <code class="os-code-inline">token</code> query parameter. Validate it server-to-server, then create your own session.</p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>routes/auth.js</span></div>
        <pre><code><span style="{{ $kw }}">const</span> express = <span style="{{ $fn }}">require</span>(<span style="{{ $str }}">'express'</span>);
<span style="{{ $kw }}">const</span> cas     = <span style="{{ $fn }}">require</span>(<span style="{{ $str }}">'../cas'</span>);
<span style="{{ $kw }}">const</span> router  = express.<span style="{{ $fn }}">Router</span>();

<span style="{{ $com }}">// 1. Send the user to CAS to sign in</span>
router.<span style="{{ $fn }}">get</span>(<span style="{{ $str }}">'/auth/login'</span>, (req, res) =&gt; {
  res.<span style="{{ $fn }}">redirect</span>(cas.<span style="{{ $fn }}">getLoginUrl</span>());
});

<span style="{{ $com }}">// 2. CAS redirects back here with ?token=&lt;JWT&gt;</span>
router.<span style="{{ $fn }}">get</span>(<span style="{{ $str }}">'/cas/callback'</span>, <span style="{{ $kw }}">async</span> (req, res) =&gt; {
  <span style="{{ $kw }}">const</span> { token } = req.query;
  <span style="{{ $kw }}">const</span> user = <span style="{{ $kw }}">await</span> cas.<span style="{{ $fn }}">validateToken</span>(token);

  <span style="{{ $kw }}">if</span> (user) {
    req.session.cas_user  = user;   <span style="{{ $com }}">// { id, username, email, roles }</span>
    req.session.cas_token = token;
    <span style="{{ $kw }}">return</span> res.<span style="{{ $fn }}">redirect</span>(<span style="{{ $str }}">'/dashboard'</span>);
  }
  res.<span style="{{ $fn }}">redirect</span>(<span style="{{ $str }}">'/auth/login?error=authentication_failed'</span>);
});

<span style="{{ $com }}">// 3. Logout invalidates the CAS session and clears the local one</span>
router.<span style="{{ $fn }}">post</span>(<span style="{{ $str }}">'/logout'</span>, <span style="{{ $kw }}">async</span> (req, res) =&gt; {
  <span style="{{ $kw }}">await</span> cas.<span style="{{ $fn }}">logout</span>(req.session.cas_token);
  req.session.<span style="{{ $fn }}">destroy</span>(() =&gt; res.<span style="{{ $fn }}">redirect</span>(<span style="{{ $str }}">'/'</span>));
});

<span style="{{ $kw }}">module.exports</span> = router;</code></pre>
    </div>
    <div class="os-alert mt-4">
        <i class="fas fa-circle-info mt-0.5 text-[var(--color-muted)]"></i>
        <div>The token is single-use. <code class="os-code-inline">validateToken()</code> posts to <code class="os-code-inline">/api/validate-token</code> with your client credentials and returns the user object on success, or <code class="os-code-inline">null</code> on failure.</div>
    </div>
</section>

<section id="middleware" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-2">5. Protect routes</h2>
    <p class="text-[var(--color-muted)] mb-4">The package ships drop-in Express middleware. <code class="os-code-inline">casAuth(cas)</code> requires an authenticated session (or a <code class="os-code-inline">Bearer</code> token) and attaches the user to <code class="os-code-inline">req.casUser</code>. <code class="os-code-inline">casRole(cas, ...roles)</code> enforces role-based access.</p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>routes/app.js</span></div>
        <pre><code><span style="{{ $kw }}">const</span> express = <span style="{{ $fn }}">require</span>(<span style="{{ $str }}">'express'</span>);
<span style="{{ $kw }}">const</span> cas     = <span style="{{ $fn }}">require</span>(<span style="{{ $str }}">'../cas'</span>);
<span style="{{ $kw }}">const</span> { casAuth, casRole } = <span style="{{ $fn }}">require</span>(<span style="{{ $str }}">'@cas-system/node-cas-client/src/middleware'</span>);
<span style="{{ $kw }}">const</span> router  = express.<span style="{{ $fn }}">Router</span>();

<span style="{{ $com }}">// Any signed-in user</span>
router.<span style="{{ $fn }}">get</span>(<span style="{{ $str }}">'/dashboard'</span>, <span style="{{ $fn }}">casAuth</span>(cas), (req, res) =&gt; {
  res.<span style="{{ $fn }}">json</span>({ message: <span style="{{ $str }}">'Welcome'</span>, user: req.casUser });
});

<span style="{{ $com }}">// Requires the "admin" role</span>
router.<span style="{{ $fn }}">get</span>(<span style="{{ $str }}">'/admin'</span>, <span style="{{ $fn }}">casAuth</span>(cas), <span style="{{ $fn }}">casRole</span>(cas, <span style="{{ $str }}">'admin'</span>), (req, res) =&gt; {
  res.<span style="{{ $fn }}">json</span>({ message: <span style="{{ $str }}">'Admin area'</span>, user: req.casUser });
});

<span style="{{ $kw }}">module.exports</span> = router;</code></pre>
    </div>
    <div class="os-alert os-alert-success mt-4">
        <i class="fas fa-circle-check mt-0.5"></i>
        <div><strong>Done.</strong> Add <code class="os-code-inline">casAuth(cas)</code> to any route that requires SSO, and chain <code class="os-code-inline">casRole(cas, 'admin')</code> when a role is required. The middleware uses the in-memory token cache, so repeat requests skip a round trip to the CAS server.</div>
    </div>
</section>

<section id="server-to-server" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-2">6. Server-to-server tokens</h2>
    <p class="text-[var(--color-muted)] mb-4">From an IP-whitelisted backend you can issue a token for a known user with <code class="os-code-inline">generateSSOToken(username)</code>. It returns the <code class="os-code-inline">token</code> and a ready-to-use <code class="os-code-inline">redirect_url</code>, or <code class="os-code-inline">null</code> on failure.</p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>provision.js</span></div>
        <pre><code><span style="{{ $kw }}">const</span> result = <span style="{{ $kw }}">await</span> cas.<span style="{{ $fn }}">generateSSOToken</span>(<span style="{{ $str }}">'john_doe'</span>);

<span style="{{ $kw }}">if</span> (result) {
  console.<span style="{{ $fn }}">log</span>(result.token);         <span style="{{ $com }}">// signed JWT</span>
  console.<span style="{{ $fn }}">log</span>(result.redirect_url);  <span style="{{ $com }}">// callback URL with the token attached</span>
}</code></pre>
    </div>
</section>

<section id="api" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-2">7. API reference</h2>
    <p class="text-[var(--color-muted)] mb-4">Methods on the <code class="os-code-inline">CasClient</code> instance.</p>
    <div class="os-card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[var(--color-line)] bg-[var(--color-surface-2)] text-left">
                    <th class="px-4 py-2.5 font-semibold text-[var(--color-ink-2)]">Method</th>
                    <th class="px-4 py-2.5 font-semibold text-[var(--color-ink-2)]">Description</th>
                </tr>
            </thead>
            <tbody class="text-[var(--color-muted)]">
                <tr class="border-b border-[var(--color-line)]"><td class="px-4 py-2.5"><code class="os-code-inline">getLoginUrl()</code></td><td class="px-4 py-2.5">Build the CAS SSO login URL to redirect the browser to.</td></tr>
                <tr class="border-b border-[var(--color-line)]"><td class="px-4 py-2.5"><code class="os-code-inline">validateToken(token)</code></td><td class="px-4 py-2.5">Validate a callback token server-to-server. Resolves to the user object or <code class="os-code-inline">null</code>.</td></tr>
                <tr class="border-b border-[var(--color-line)]"><td class="px-4 py-2.5"><code class="os-code-inline">getUserFromToken(token)</code></td><td class="px-4 py-2.5">Return cached user data for a previously validated token.</td></tr>
                <tr class="border-b border-[var(--color-line)]"><td class="px-4 py-2.5"><code class="os-code-inline">generateSSOToken(username)</code></td><td class="px-4 py-2.5">Issue a token for a user via client credentials (server-to-server).</td></tr>
                <tr class="border-b border-[var(--color-line)]"><td class="px-4 py-2.5"><code class="os-code-inline">logout(token?)</code></td><td class="px-4 py-2.5">Log out from the CAS server and drop the cached token.</td></tr>
                <tr class="border-b border-[var(--color-line)]"><td class="px-4 py-2.5"><code class="os-code-inline">userHasRole(user, role)</code></td><td class="px-4 py-2.5">Check whether the user has a single role.</td></tr>
                <tr class="border-b border-[var(--color-line)]"><td class="px-4 py-2.5"><code class="os-code-inline">userHasAnyRole(user, roles)</code></td><td class="px-4 py-2.5">Check whether the user has any of the given roles.</td></tr>
                <tr><td class="px-4 py-2.5"><code class="os-code-inline">userHasAllRoles(user, roles)</code></td><td class="px-4 py-2.5">Check whether the user has all of the given roles.</td></tr>
            </tbody>
        </table>
    </div>
    <p class="text-sm text-[var(--color-muted)] mt-4">The validation endpoint <code class="os-code-inline">POST /api/validate-token</code> responds with <code class="os-code-inline">{ valid: true, user: { id, username, email }, expires_at }</code> on success, or <code class="os-code-inline">401 { error }</code> on failure.</p>
</section>

<section class="border-t border-[var(--color-line)] pt-10">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-4">Next steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="os-card os-card-hover flex items-center gap-3 p-4"><i class="fas fa-code text-[var(--color-muted)] text-sm"></i><span class="text-sm font-medium text-[var(--color-ink-2)]">API reference</span></a>
        <a href="{{ route('docs.security') }}" class="os-card os-card-hover flex items-center gap-3 p-4"><i class="fas fa-shield-halved text-[var(--color-muted)] text-sm"></i><span class="text-sm font-medium text-[var(--color-ink-2)]">Security guide</span></a>
        <a href="{{ route('docs.webhooks') }}" class="os-card os-card-hover flex items-center gap-3 p-4"><i class="fas fa-bolt text-[var(--color-muted)] text-sm"></i><span class="text-sm font-medium text-[var(--color-ink-2)]">Webhooks</span></a>
    </div>
</section>
@endsection
