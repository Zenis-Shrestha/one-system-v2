@extends('public.documentation.layout')

@section('title', 'JavaScript SDK — One System CAS SSO')
@section('description', 'Browser JavaScript SDK for integrating SPAs and plain HTML pages with One System CAS SSO.')

@section('content')
<section class="border-b border-[var(--color-line)] pb-10 mb-12">
    <div class="">
        <div class="flex items-center gap-4 mb-5">
            <span class="os-icon-tile os-icon-tile-ink"><i class="fab fa-js"></i></span>
            <div>
                <p class="os-eyebrow mb-1">Integration guide</p>
                <h1 class="text-3xl font-bold text-[var(--color-ink)] tracking-tight">JavaScript SDK</h1>
            </div>
        </div>
        <p class="text-lg text-[var(--color-muted)] leading-relaxed mb-5">{{ $javascriptGuide['description'] }}</p>
        <div class="flex flex-wrap gap-2">
            <span class="os-badge"><i class="fas fa-clock"></i> 5 min read</span>
            <span class="os-badge"><i class="fas fa-signal"></i> Beginner</span>
            <span class="os-badge"><i class="fas fa-cube"></i> @cas-system/js-cas-client v1.0.0</span>
            <span class="os-badge"><i class="fas fa-box"></i> Zero dependencies</span>
        </div>
    </div>
</section>

<nav class="mb-12 os-card os-card-pad">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-3">On this page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#install" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">1. Install the SDK</a></li>
        <li><a href="#initialize" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">2. Initialize the client</a></li>
        <li><a href="#login" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">3. Trigger SSO login</a></li>
        <li><a href="#callback" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">4. Handle the callback</a></li>
        <li><a href="#backend" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">5. Backend validation contract</a></li>
        <li><a href="#session" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">6. Session, roles &amp; logout</a></li>
        <li><a href="#spa" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">7. SPA route guard</a></li>
        <li><a href="#api" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">8. API reference</a></li>
    </ol>
</nav>

<section id="install" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-3">1. Install the SDK</h2>
    <p class="text-sm text-[var(--color-ink-2)] mb-5">The <code class="os-code-inline">@cas-system/js-cas-client</code> package is a lightweight, zero-dependency browser SDK. It needs no build step. Install it from npm, or drop it in with a single <code class="os-code-inline">&lt;script&gt;</code> tag (UMD: exposes a global <code class="os-code-inline">CasClient</code>).</p>

    <div class="os-codeblock mb-5">
        <div class="os-codeblock-head"><span>npm</span><span>terminal</span></div>
        <pre><code>npm install @cas-system/js-cas-client</code></pre>
    </div>

    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>Script tag (CDN)</span><span>index.html</span></div>
        <pre><code>&lt;script src=&quot;https://your-cas-server.com/assets/js/cas-client.js&quot;&gt;&lt;/script&gt;</code></pre>
    </div>
</section>

<section id="initialize" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-3">2. Initialize the client</h2>
    <p class="text-sm text-[var(--color-ink-2)] mb-5">Create a single <code class="os-code-inline">CasClient</code> instance with your server URL and registered client ID. The <code class="os-code-inline">callbackUrl</code> must match the callback URL registered for your client, and <code class="os-code-inline">backendValidateUrl</code> points to the endpoint on <em>your own</em> server that validates tokens.</p>

    <div class="os-codeblock mb-5">
        <div class="os-codeblock-head"><span>ES module import</span><span>cas.js</span></div>
        <pre><code>import CasClient from '@cas-system/js-cas-client';

const cas = new CasClient({
  serverUrl: 'https://your-cas-server.com',
  clientId: 'your_client_id',
  callbackUrl: 'https://your-app.com/cas/callback',
  backendValidateUrl: '/api/auth/validate', // your server, not the CAS server
});</code></pre>
    </div>

    <p class="text-sm text-[var(--color-ink-2)] mb-3">Using the global build instead? The constructor is identical:</p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>Script tag usage</span><span>index.html</span></div>
        <pre><code>&lt;script src=&quot;https://your-cas-server.com/assets/js/cas-client.js&quot;&gt;&lt;/script&gt;
&lt;script&gt;
  var cas = new CasClient({
    serverUrl: 'https://your-cas-server.com',
    clientId: 'your_client_id',
    callbackUrl: 'https://your-app.com/cas/callback',
    backendValidateUrl: '/api/auth/validate',
  });
&lt;/script&gt;</code></pre>
    </div>

    <div class="os-alert mt-5">
        <i class="fas fa-info-circle text-[var(--color-accent)] mt-0.5"></i>
        <div>
            <strong class="text-[var(--color-ink)]">Defaults.</strong> If you omit <code class="os-code-inline">callbackUrl</code> it falls back to <code class="os-code-inline">window.location.origin + '/cas/callback'</code>, and <code class="os-code-inline">backendValidateUrl</code> defaults to <code class="os-code-inline">/api/auth/validate</code>. <code class="os-code-inline">serverUrl</code> and <code class="os-code-inline">clientId</code> are required and throw if missing.
        </div>
    </div>
</section>

<section id="login" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-3">3. Trigger SSO login</h2>
    <p class="text-sm text-[var(--color-ink-2)] mb-5">Call <code class="os-code-inline">cas.login()</code> to redirect the browser to <code class="os-code-inline">GET {serverUrl}/sso/login?client_id={clientId}</code>. The CAS server authenticates the user and redirects back to your registered callback URL with the token appended as <code class="os-code-inline">?token={JWT}</code>. Pass a return URL to <code class="os-code-inline">login()</code> and the SDK stashes it (in <code class="os-code-inline">sessionStorage</code>) so you can send the user there after the callback completes.</p>

    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>Login button</span><span>index.html</span></div>
        <pre><code>&lt;button onclick=&quot;cas.login()&quot;&gt;Sign in with One System&lt;/button&gt;

&lt;!-- Or stash a return URL for after the callback --&gt;
&lt;button onclick=&quot;cas.login('/dashboard')&quot;&gt;Sign in&lt;/button&gt;</code></pre>
    </div>

    <p class="text-sm text-[var(--color-ink-2)] mt-5">Need the URL without redirecting (e.g. to render an anchor)? Use <code class="os-code-inline">cas.getLoginUrl()</code>.</p>
</section>

<section id="callback" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-3">4. Handle the callback</h2>
    <p class="text-sm text-[var(--color-ink-2)] mb-5">On your registered callback page, call <code class="os-code-inline">cas.handleCallback()</code>. It extracts the <code class="os-code-inline">token</code> query parameter, sends it to your <code class="os-code-inline">backendValidateUrl</code> for server-side validation, stores the returned user in <code class="os-code-inline">sessionStorage</code>, and resolves with the user object (or <code class="os-code-inline">null</code> on failure).</p>

    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>Callback page</span><span>cas/callback.html</span></div>
        <pre><code>&lt;script&gt;
  cas.handleCallback().then(function (user) {
    if (user) {
      console.log('Welcome,', user.username);
      // consumeReturnUrl() reads + clears the URL stashed by login()
      window.location.href = cas.consumeReturnUrl() || '/dashboard';
    } else {
      alert('Login failed');
      window.location.href = '/login';
    }
  });
&lt;/script&gt;</code></pre>
    </div>

    <div class="os-alert os-alert-warning mt-5">
        <i class="fas fa-triangle-exclamation mt-0.5"></i>
        <div>
            <strong>Never validate tokens in the browser.</strong> Validation requires your <code class="os-code-inline">client_secret</code>, which must never reach client-side code. The SDK only forwards the token to <em>your</em> backend &mdash; it never holds the secret and never calls the CAS validation endpoint directly. The token is <strong>single-use</strong>, so validate it once and then create your app's own session.
        </div>
    </div>
</section>

<section id="backend" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-3">5. Backend validation contract</h2>
    <p class="text-sm text-[var(--color-ink-2)] mb-5"><code class="os-code-inline">handleCallback()</code> (via <code class="os-code-inline">validateTokenViaBackend(token)</code>) sends <code class="os-code-inline">POST {backendValidateUrl}</code> with body <code class="os-code-inline">{ "token": "&lt;jwt&gt;" }</code>. Your backend then validates the token <strong>server-to-server</strong> against the CAS server and returns a JSON body containing a <code class="os-code-inline">user</code> object back to the browser.</p>

    <div class="os-codeblock mb-5">
        <div class="os-codeblock-head"><span>Browser &rarr; your backend</span><span>POST {backendValidateUrl}</span></div>
        <pre><code>POST /api/auth/validate          Content-Type: application/json

{ "token": "&lt;jwt&gt;" }</code></pre>
    </div>

    <p class="text-sm text-[var(--color-ink-2)] mb-3">Your backend validates the token against the CAS server using your client credentials:</p>
    <div class="os-codeblock mb-5">
        <div class="os-codeblock-head"><span>Your backend &rarr; CAS server</span><span>POST {CAS_BASE}/api/validate-token</span></div>
        <pre><code>POST https://your-cas-server.com/api/validate-token
Content-Type: application/json

{
  "token": "&lt;jwt&gt;",
  "client_id": "your_client_id",
  "client_secret": "your_client_secret"
}

// 200 OK
{
  "valid": true,
  "user": { "id": 1, "username": "jdoe", "email": "jdoe@example.com" },
  "expires_at": "2026-06-18T12:00:00Z"
}

// 401 Unauthorized
{ "error": "Invalid or expired token" }</code></pre>
    </div>

    <p class="text-sm text-[var(--color-ink-2)]">On success, return a JSON body with a <code class="os-code-inline">user</code> object to the browser. The SDK reads <code class="os-code-inline">data.user</code>, caches it, and resolves the promise with it.</p>

    <div class="os-alert mt-5">
        <i class="fas fa-shield-halved text-[var(--color-accent)] mt-0.5"></i>
        <div>
            <strong class="text-[var(--color-ink)]">Hardening.</strong> Client secrets are stored <strong>hashed</strong> and shown only once on creation or regeneration. Serve every endpoint over HTTPS in production, and set <code class="os-code-inline">JWT_SECRET</code>, <code class="os-code-inline">RECAPTCHAV3_*</code>, and <code class="os-code-inline">CORS_ALLOWED_ORIGINS</code> via environment variables.
        </div>
    </div>
</section>

<section id="session" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-3">6. Session, roles &amp; logout</h2>
    <p class="text-sm text-[var(--color-ink-2)] mb-5">After a successful callback, the user is cached in <code class="os-code-inline">sessionStorage</code> and survives page navigation. Read it with <code class="os-code-inline">getUser()</code>, gate UI on roles, and clear it with <code class="os-code-inline">logout()</code>.</p>

    <div class="os-codeblock mb-5">
        <div class="os-codeblock-head"><span>Protected page</span><span>dashboard.html</span></div>
        <pre><code>&lt;script&gt;
  if (!cas.isAuthenticated()) {
    cas.login(window.location.href);
  }

  var user = cas.getUser();
  document.getElementById('username').textContent = user.username;

  // Role-based UI
  if (cas.userHasRole('admin')) {
    document.getElementById('admin-panel').style.display = 'block';
  }
&lt;/script&gt;</code></pre>
    </div>

    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>Logout</span><span>index.html</span></div>
        <pre><code>&lt;!-- Clears the cached user, POSTs {serverUrl}/api/logout, then redirects --&gt;
&lt;button onclick=&quot;cas.logout('/')&quot;&gt;Sign out&lt;/button&gt;</code></pre>
    </div>
</section>

<section id="spa" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-3">7. SPA route guard</h2>
    <p class="text-sm text-[var(--color-ink-2)] mb-5">For React, Vue, or Angular apps, wrap protected routes in a guard that checks <code class="os-code-inline">cas.isAuthenticated()</code> and redirects to SSO login when there is no session.</p>

    <div class="os-codeblock mb-5">
        <div class="os-codeblock-head"><span>React route guard</span><span>ProtectedRoute.jsx</span></div>
        <pre><code>function ProtectedRoute({ children }) {
  if (!cas.isAuthenticated()) {
    cas.login(window.location.href);
    return null;
  }
  return children;
}

// Usage
&lt;Route
  path=&quot;/dashboard&quot;
  element={&lt;ProtectedRoute&gt;&lt;Dashboard /&gt;&lt;/ProtectedRoute&gt;}
/&gt;</code></pre>
    </div>

    <div class="os-alert">
        <i class="fas fa-circle-info text-[var(--color-accent)] mt-0.5"></i>
        <div>
            Building with React hooks or context? The dedicated
            <a href="{{ route('docs.react') }}" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] underline underline-offset-2">React integration guide</a>
            covers <code class="os-code-inline">CasProvider</code>, <code class="os-code-inline">useCasAuth</code>, and protected components.
        </div>
    </div>
</section>

<section id="api" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-3">8. API reference</h2>
    <p class="text-sm text-[var(--color-ink-2)] mb-5">Every method on a <code class="os-code-inline">CasClient</code> instance.</p>

    <div class="os-card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[var(--color-surface-2)] text-left">
                    <th class="px-4 py-2.5 font-semibold text-[var(--color-ink-2)] border-b border-[var(--color-line)]">Method</th>
                    <th class="px-4 py-2.5 font-semibold text-[var(--color-ink-2)] border-b border-[var(--color-line)]">Description</th>
                </tr>
            </thead>
            <tbody class="text-[var(--color-ink-2)]">
                <tr>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)] align-top"><code class="os-code-inline">login(returnUrl?)</code></td>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)]">Redirect to CAS login; stashes <code class="os-code-inline">returnUrl</code> for after the callback.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)] align-top"><code class="os-code-inline">getLoginUrl()</code></td>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)]">Build the login URL without redirecting.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)] align-top"><code class="os-code-inline">consumeReturnUrl()</code></td>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)]">Read and clear the <code class="os-code-inline">returnUrl</code> stashed by <code class="os-code-inline">login()</code>.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)] align-top"><code class="os-code-inline">handleCallback()</code></td>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)]">Extract the token from the URL and validate it via your backend. Returns <code class="os-code-inline">Promise&lt;user|null&gt;</code>.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)] align-top"><code class="os-code-inline">extractTokenFromUrl()</code></td>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)]">Read the <code class="os-code-inline">token</code> query parameter from the current URL.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)] align-top"><code class="os-code-inline">validateTokenViaBackend(token)</code></td>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)]">POST the token to <code class="os-code-inline">backendValidateUrl</code> for server-side validation.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)] align-top"><code class="os-code-inline">getUser()</code></td>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)]">Return the cached user object (or <code class="os-code-inline">null</code>).</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)] align-top"><code class="os-code-inline">isAuthenticated()</code></td>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)]">Whether a user session is stored.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)] align-top"><code class="os-code-inline">logout(redirectUrl?)</code></td>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)]">Clear the session, POST to <code class="os-code-inline">{serverUrl}/api/logout</code>, then redirect.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)] align-top"><code class="os-code-inline">userHasRole(role)</code></td>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)]">Check a single role on the cached user.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)] align-top"><code class="os-code-inline">userHasAnyRole(roles)</code></td>
                    <td class="px-4 py-2.5 border-b border-[var(--color-line)]">Check whether the user has any of the given roles.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 align-top"><code class="os-code-inline">userHasAllRoles(roles)</code></td>
                    <td class="px-4 py-2.5">Check whether the user has all of the given roles.</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="border-t border-[var(--color-line)] pt-10">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-4">Next steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="os-card os-card-hover flex items-center gap-3 p-4">
            <i class="fas fa-code text-[var(--color-muted)]"></i>
            <span class="text-sm font-medium text-[var(--color-ink-2)]">API reference</span>
        </a>
        <a href="{{ route('docs.security') }}" class="os-card os-card-hover flex items-center gap-3 p-4">
            <i class="fas fa-shield-halved text-[var(--color-muted)]"></i>
            <span class="text-sm font-medium text-[var(--color-ink-2)]">Security guide</span>
        </a>
        <a href="{{ route('docs.webhooks') }}" class="os-card os-card-hover flex items-center gap-3 p-4">
            <i class="fas fa-bolt text-[var(--color-muted)]"></i>
            <span class="text-sm font-medium text-[var(--color-ink-2)]">Webhooks</span>
        </a>
    </div>
</section>
@endsection
