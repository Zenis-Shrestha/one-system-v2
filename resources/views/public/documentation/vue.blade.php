@extends('public.documentation.layout')

@section('title', 'Vue 3 Integration — CAS SSO')
@section('description', 'Complete guide for integrating Vue 3 applications with CAS Single Sign-On using the @cas-system/vue-cas-client SDK — plugin, composables, router guards, and Pinia store.')

@section('content')
<section class="border-b border-[var(--color-line)] pb-10 mb-12">
    <div class="">
        <div class="flex items-center gap-3 mb-4">
            <div class="os-icon-tile os-icon-tile-ink">
                <i class="fab fa-vuejs"></i>
            </div>
            <div>
                <p class="os-eyebrow">Integration guide</p>
                <h1 class="text-3xl font-bold text-[var(--color-ink)] tracking-tight leading-tight">Vue 3</h1>
            </div>
        </div>
        <p class="text-lg text-[var(--color-muted)] leading-relaxed mb-4">{{ $vueGuide['description'] }}</p>
        <div class="flex flex-wrap gap-4 text-xs text-[var(--color-faint)]">
            <span><i class="fas fa-clock mr-1"></i>5 min setup</span>
            <span><i class="fas fa-signal mr-1"></i>Easy</span>
            <span><i class="fas fa-tag mr-1"></i>Vue 3.4+ · Composition API</span>
        </div>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-[var(--color-line)] bg-[var(--color-surface-2)]">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-3">On this page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#overview" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">1. Overview</a></li>
        <li><a href="#installation" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">2. {{ $vueGuide['sections']['setup'] }}</a></li>
        <li><a href="#configuration" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">3. Configuration</a></li>
        <li><a href="#plugin" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">4. {{ $vueGuide['sections']['plugin'] }}</a></li>
        <li><a href="#composables" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">5. {{ $vueGuide['sections']['composables'] }}</a></li>
        <li><a href="#router" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">6. {{ $vueGuide['sections']['router'] }}</a></li>
        <li><a href="#pinia" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">7. {{ $vueGuide['sections']['pinia'] }}</a></li>
        <li><a href="#example" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)]">8. End-to-end {{ $vueGuide['sections']['examples'] }}</a></li>
    </ol>
</nav>

<section id="overview" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">1. Overview</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        <code class="os-code-inline">@cas-system/vue-cas-client</code> is a Vue 3 SDK for the CAS Single
        Sign-On flow. It ships a plugin (<code class="os-code-inline">app.use(CasPlugin, config)</code>),
        the <code class="os-code-inline">useCasAuth()</code> and <code class="os-code-inline">useCasUser()</code>
        composables, a factory-based Vue Router guard, an optional Pinia store, and the
        <code class="os-code-inline">&lt;CasProtectedView&gt;</code> slot component. All exports are named, so the
        package is tree-shakeable.
    </p>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-6">
        The SSO flow has three moving parts: the browser redirects to the CAS server, the server redirects
        back to your registered callback with a single-use <code class="os-code-inline">?token=&lt;JWT&gt;</code>,
        and your <strong>backend</strong> validates that token server-to-server. The <code class="os-code-inline">client_secret</code>
        never touches the browser.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>Browser SSO flow</span></div>
        <pre><code>1. login()              → redirect to {CAS_BASE}/sso/login?client_id=...
2. CAS authenticates    → 302 to your callback_url?token=&lt;JWT&gt;
3. handleCallback()     → POST { token } to your backend
4. backend validates    → POST {CAS_BASE}/api/validate-token
                          { token, client_id, client_secret }  → 200 { valid, user }
5. session created      → user persisted in sessionStorage</code></pre>
    </div>
    <div class="os-alert os-alert-warning">
        <div class="flex items-start gap-2">
            <i class="fas fa-shield-halved mt-0.5"></i>
            <div class="text-sm">
                <strong>Single-use tokens.</strong> The JWT returned on the callback is validated exactly once,
                server-side, and then exchanged for your application's own session. Serve everything over HTTPS in
                production.
            </div>
        </div>
    </div>
</section>

<section id="installation" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">2. {{ $vueGuide['sections']['setup'] }}</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        Install from the registry, or reference the package by local path while it lives in your monorepo at
        <code class="os-code-inline">packages/vue-cas-client</code>. It requires <code class="os-code-inline">vue ≥ 3.4</code>;
        <code class="os-code-inline">vue-router ≥ 4.4</code> and <code class="os-code-inline">pinia ≥ 2.2</code> are optional peers.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>Terminal — package manager</span></div>
        <pre><code># npm
npm install @cas-system/vue-cas-client

# yarn
yarn add @cas-system/vue-cas-client

# pnpm
pnpm add @cas-system/vue-cas-client</code></pre>
    </div>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>package.json — local path (monorepo)</span></div>
        <pre><code>{
  "dependencies": {
    "@cas-system/vue-cas-client": "file:../packages/vue-cas-client"
  }
}</code></pre>
    </div>
</section>

<section id="configuration" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">3. Configuration</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        The SDK runs in the browser, so only public values belong in your client env. The
        <code class="os-code-inline">CLIENT_SECRET</code> stays on the server (used only for the
        server-to-server validation call).
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>.env — Vue app (Vite, public)</span></div>
        <pre><code>VITE_CAS_BASE_URL=https://cas.example.com
VITE_CAS_CLIENT_ID=my-vue-app
VITE_CAS_CALLBACK_URL=https://my-app.com/auth/callback
# backend endpoint the SDK POSTs the token to (proxies validation):
VITE_CAS_VALIDATE_URL=/api/auth/validate</code></pre>
    </div>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>.env — backend (secret, server-only)</span></div>
        <pre><code>CAS_BASE=https://cas.example.com
CAS_CLIENT_ID=my-vue-app
CAS_CLIENT_SECRET=your_hashed_client_secret   # shown once on creation/regeneration</code></pre>
    </div>
    <div class="rounded-xl border border-[var(--color-line)] overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[var(--color-surface-2)] text-[var(--color-muted)]">
                <tr>
                    <th class="text-left font-medium px-4 py-2.5">CasConfig field</th>
                    <th class="text-left font-medium px-4 py-2.5">Required</th>
                    <th class="text-left font-medium px-4 py-2.5">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--color-line)] text-[var(--color-ink-2)]">
                <tr>
                    <td class="px-4 py-2.5"><code class="os-code-inline">serverUrl</code></td>
                    <td class="px-4 py-2.5">Yes</td>
                    <td class="px-4 py-2.5">CAS server origin ({{ '{CAS_BASE}' }}), no trailing slash.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5"><code class="os-code-inline">clientId</code></td>
                    <td class="px-4 py-2.5">Yes</td>
                    <td class="px-4 py-2.5">The <code class="os-code-inline">client_id</code> registered on the CAS server.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5"><code class="os-code-inline">callbackUrl</code></td>
                    <td class="px-4 py-2.5">No</td>
                    <td class="px-4 py-2.5">Defaults to <code class="os-code-inline">{{ '{origin}' }}/auth/callback</code>. Must match the registered callback.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5"><code class="os-code-inline">backendValidateUrl</code></td>
                    <td class="px-4 py-2.5">Yes*</td>
                    <td class="px-4 py-2.5">Your backend endpoint that proxies token validation. Required to validate.</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section id="plugin" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">4. {{ $vueGuide['sections']['plugin'] }}</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        Install <code class="os-code-inline">CasPlugin</code> once in <code class="os-code-inline">main.ts</code>. It
        provides the reactive auth context that the composables and router guard read from. By default
        (<code class="os-code-inline">autoHandleCallback: true</code>) the plugin will detect a <code class="os-code-inline">?token=</code>
        in the URL on install and validate it automatically.
    </p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>main.ts</span></div>
        <pre><code>import { createApp } from 'vue';
import { CasPlugin } from '@cas-system/vue-cas-client';
import App from './App.vue';

const app = createApp(App);

app.use(CasPlugin, {
  serverUrl:          import.meta.env.VITE_CAS_BASE_URL,
  clientId:           import.meta.env.VITE_CAS_CLIENT_ID,
  callbackUrl:        import.meta.env.VITE_CAS_CALLBACK_URL,
  backendValidateUrl: import.meta.env.VITE_CAS_VALIDATE_URL, // '/api/auth/validate'
  // autoHandleCallback: true, // default — set false to handle callbacks manually
});

app.mount('#app');</code></pre>
    </div>
</section>

<section id="composables" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">5. {{ $vueGuide['sections']['composables'] }}</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        <code class="os-code-inline">useCasAuth()</code> exposes the reactive auth state and the
        <code class="os-code-inline">login</code> / <code class="os-code-inline">logout</code> /
        <code class="os-code-inline">handleCallback</code> actions. Call it inside any component below the plugin.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>components/AuthButton.vue</span></div>
        <pre><code>&lt;script setup lang="ts"&gt;
import { useCasAuth } from '@cas-system/vue-cas-client';

const { user, isAuthenticated, isLoading, login, logout } = useCasAuth();
&lt;/script&gt;

&lt;template&gt;
  &lt;div v-if="isLoading"&gt;Checking session…&lt;/div&gt;
  &lt;div v-else-if="isAuthenticated"&gt;
    &lt;p&gt;Welcome, &#123;&#123; user?.username &#125;&#125;&lt;/p&gt;
    &lt;button @click="logout()"&gt;Logout&lt;/button&gt;
  &lt;/div&gt;
  &lt;button v-else @click="login()"&gt;Login with SSO&lt;/button&gt;
&lt;/template&gt;</code></pre>
    </div>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        <code class="os-code-inline">useCasUser()</code> is a user-focused composable with reactive role checks —
        <code class="os-code-inline">hasRole</code>, <code class="os-code-inline">hasAnyRole</code>, and
        <code class="os-code-inline">hasAllRoles</code> each return a <code class="os-code-inline">ComputedRef&lt;boolean&gt;</code>.
    </p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>components/AppNav.vue</span></div>
        <pre><code>&lt;script setup lang="ts"&gt;
import { useCasUser } from '@cas-system/vue-cas-client';

const { hasRole, hasAnyRole } = useCasUser();

const isAdmin = hasRole('admin');
const canManageContent = hasAnyRole(['admin', 'editor']);
&lt;/script&gt;

&lt;template&gt;
  &lt;nav&gt;
    &lt;RouterLink to="/"&gt;Home&lt;/RouterLink&gt;
    &lt;RouterLink v-if="isAdmin" to="/admin"&gt;Admin&lt;/RouterLink&gt;
    &lt;RouterLink v-if="canManageContent" to="/editor"&gt;Editor&lt;/RouterLink&gt;
  &lt;/nav&gt;
&lt;/template&gt;</code></pre>
    </div>
</section>

<section id="router" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">6. {{ $vueGuide['sections']['router'] }}</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        Mark protected routes with <code class="os-code-inline">meta.requiresAuth</code> (and optional
        <code class="os-code-inline">meta.roles</code>), then attach <code class="os-code-inline">createCasAuthGuard()</code>.
        Pull the shared CAS context off the app's provides using the exported
        <code class="os-code-inline">CAS_AUTH_KEY</code>.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>router/index.ts</span></div>
        <pre><code>import { createRouter, createWebHistory } from 'vue-router';

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: () =&gt; import('../views/Home.vue') },
    { path: '/auth/callback', component: () =&gt; import('../views/AuthCallback.vue') },
    {
      path: '/dashboard',
      component: () =&gt; import('../views/Dashboard.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/admin',
      component: () =&gt; import('../views/Admin.vue'),
      meta: { requiresAuth: true, roles: ['admin'] },
    },
  ],
});</code></pre>
    </div>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>main.ts — attach the guard</span></div>
        <pre><code>import { CasPlugin, createCasAuthGuard, CAS_AUTH_KEY } from '@cas-system/vue-cas-client';
import { router } from './router';

app.use(CasPlugin, {
  serverUrl:          import.meta.env.VITE_CAS_BASE_URL,
  clientId:           import.meta.env.VITE_CAS_CLIENT_ID,
  backendValidateUrl: import.meta.env.VITE_CAS_VALIDATE_URL,
});

// Read the reactive CAS context the plugin provided
const casContext = app._context.provides[CAS_AUTH_KEY as symbol];

router.beforeEach(createCasAuthGuard(casContext, {
  redirectToLogin: true, // redirect to CAS /sso/login when unauthenticated
}));

app.use(router);
app.mount('#app');</code></pre>
    </div>
</section>

<section id="pinia" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">7. {{ $vueGuide['sections']['pinia'] }}</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        The <code class="os-code-inline">useCasStore()</code> Pinia store is an optional alternative for apps that
        already centralise state in Pinia. Call <code class="os-code-inline">init(config)</code> once, then use its
        state, getters, and actions directly.
    </p>
    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>App.vue — Pinia store</span></div>
        <pre><code>&lt;script setup lang="ts"&gt;
import { onMounted } from 'vue';
import { useCasStore } from '@cas-system/vue-cas-client';

const auth = useCasStore();

onMounted(() =&gt; {
  auth.init({
    serverUrl:          import.meta.env.VITE_CAS_BASE_URL,
    clientId:           import.meta.env.VITE_CAS_CLIENT_ID,
    backendValidateUrl: import.meta.env.VITE_CAS_VALIDATE_URL,
  });
});
&lt;/script&gt;

&lt;template&gt;
  &lt;div v-if="auth.isAuthenticated"&gt;
    &lt;p&gt;Hello, &#123;&#123; auth.currentUser?.username &#125;&#125;&lt;/p&gt;
    &lt;p v-if="auth.hasRole('admin')"&gt;You are an admin&lt;/p&gt;
    &lt;button @click="auth.logout()"&gt;Logout&lt;/button&gt;
  &lt;/div&gt;
  &lt;button v-else @click="auth.login()"&gt;Login&lt;/button&gt;
&lt;/template&gt;</code></pre>
    </div>
</section>

<section id="example" class="mb-12">
    <h2 class="text-xl font-bold text-[var(--color-ink)] mb-4">8. End-to-end {{ $vueGuide['sections']['examples'] }}</h2>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-6">
        A complete round trip: the user clicks login, CAS redirects back to the callback view, the SDK posts the
        token to your backend, and the backend validates it against the CAS server using the secret. This honors
        the single-use token rule — validate once, then create the app's own session.
    </p>

    <h3 class="text-base font-semibold text-[var(--color-ink-2)] mb-3">Step 1 — Callback view</h3>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>views/AuthCallback.vue</span></div>
        <pre><code>&lt;script setup lang="ts"&gt;
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCasAuth } from '@cas-system/vue-cas-client';

const router = useRouter();
const { handleCallback, error } = useCasAuth();

onMounted(async () =&gt; {
  try {
    // Reads ?token= from the URL, POSTs it to backendValidateUrl,
    // persists the user, and strips the token from the address bar.
    await handleCallback();
    router.replace('/dashboard');
  } catch (e) {
    console.error('Auth failed:', e);
  }
});
&lt;/script&gt;

&lt;template&gt;
  &lt;p v-if="error"&gt;&#123;&#123; error &#125;&#125;&lt;/p&gt;
  &lt;p v-else&gt;Authenticating…&lt;/p&gt;
&lt;/template&gt;</code></pre>
    </div>

    <h3 class="text-base font-semibold text-[var(--color-ink-2)] mb-3">Step 2 — Backend validation endpoint</h3>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        The SDK <code class="os-code-inline">POST</code>s <code class="os-code-inline">{ token }</code> to
        <code class="os-code-inline">backendValidateUrl</code>. Your backend adds the
        <code class="os-code-inline">client_id</code> + <code class="os-code-inline">client_secret</code> and calls
        the CAS server. Note the protocol endpoint is
        <code class="os-code-inline">POST {{ '{CAS_BASE}' }}/api/validate-token</code>.
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>backend — Node.js / Express</span></div>
        <pre><code>// POST /api/auth/validate   body: { token }
app.post('/api/auth/validate', async (req, res) =&gt; {
  const { token } = req.body;

  const casRes = await fetch(`${process.env.CAS_BASE}/api/validate-token`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      token,
      client_id:     process.env.CAS_CLIENT_ID,
      client_secret: process.env.CAS_CLIENT_SECRET, // never sent to the browser
    }),
  });

  if (!casRes.ok) {
    return res.status(401).json({ error: 'Invalid token' });
  }

  // CAS replies: { valid: true, user: { id, username, email }, expires_at }
  const { user } = await casRes.json();

  // Token is single-use — create your own app session here, then:
  return res.json({ user });
});</code></pre>
    </div>
    <p class="text-[var(--color-ink-2)] leading-relaxed mb-4">
        Or, if your backend is Laravel:
    </p>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>backend — Laravel (routes/api.php)</span></div>
        <pre><code>Route::post('/auth/validate', function (Request $request) {
    $response = Http::post(config('cas.base') . '/api/validate-token', [
        'token'         =&gt; $request-&gt;input('token'),
        'client_id'     =&gt; config('cas.client_id'),
        'client_secret' =&gt; config('cas.client_secret'),
    ]);

    if ($response-&gt;failed()) {
        return response()-&gt;json(['error' =&gt; 'Invalid token'], 401);
    }

    // { valid, user: { id, username, email }, expires_at }
    return response()-&gt;json(['user' =&gt; $response-&gt;json('user')]);
});</code></pre>
    </div>

    <h3 class="text-base font-semibold text-[var(--color-ink-2)] mb-3">Step 3 — Protect a view declaratively</h3>
    <div class="os-codeblock mb-6">
        <div class="os-codeblock-head"><span>views/Dashboard.vue</span></div>
        <pre><code>&lt;script setup lang="ts"&gt;
import { CasProtectedView } from '@cas-system/vue-cas-client';
&lt;/script&gt;

&lt;template&gt;
  &lt;CasProtectedView :roles="['admin']" redirect&gt;
    &lt;AdminPanel /&gt;

    &lt;template #fallback&gt;
      &lt;p&gt;You do not have permission to view this.&lt;/p&gt;
    &lt;/template&gt;

    &lt;template #loading&gt;
      &lt;p&gt;Loading…&lt;/p&gt;
    &lt;/template&gt;
  &lt;/CasProtectedView&gt;
&lt;/template&gt;</code></pre>
    </div>

    <div class="os-alert os-alert-success">
        <div class="flex items-start gap-2">
            <i class="fas fa-circle-check mt-0.5"></i>
            <div class="text-sm">
                <strong>Done.</strong> Use <code class="os-code-inline">useCasAuth()</code> for state and actions,
                guard routes with <code class="os-code-inline">createCasAuthGuard()</code>, and keep the
                <code class="os-code-inline">client_secret</code> on the server behind your
                <code class="os-code-inline">backendValidateUrl</code> endpoint.
            </div>
        </div>
    </div>
</section>

<section class="border-t border-[var(--color-line)] pt-10">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-4">Next steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-[var(--color-line)] hover:border-[var(--color-line-strong)] hover:bg-[var(--color-surface-2)] transition-all"><i class="fas fa-code text-[var(--color-muted)] text-sm"></i><span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">API reference</span></a>
        <a href="{{ route('docs.security') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-[var(--color-line)] hover:border-[var(--color-line-strong)] hover:bg-[var(--color-surface-2)] transition-all"><i class="fas fa-shield-halved text-[var(--color-muted)] text-sm"></i><span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">Security guide</span></a>
        <a href="{{ route('docs.webhooks') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-[var(--color-line)] hover:border-[var(--color-line-strong)] hover:bg-[var(--color-surface-2)] transition-all"><i class="fas fa-bolt text-[var(--color-muted)] text-sm"></i><span class="text-sm font-medium text-[var(--color-ink-2)] group-hover:text-[var(--color-ink)]">Webhooks</span></a>
    </div>
</section>
@endsection
