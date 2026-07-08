# @cas-system/vue-cas-client

> Vue 3 CAS (Central Authentication System) Client SDK — composables, plugin, router guards, Pinia store, and components for seamless SSO integration.

---

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Auth Flow](#auth-flow)
- [Quick Start](#quick-start)
- [Plugin Setup](#plugin-setup)
- [Composables](#composables)
  - [useCasAuth](#usecasauth)
  - [useCasUser](#usecasuser)
- [Vue Router Integration](#vue-router-integration)
- [Pinia Store](#pinia-store)
- [CasProtectedView Component](#casprotectedview-component)
- [Backend Setup](#backend-setup)
- [API Reference](#api-reference)
- [Examples](#examples)

---

## Features

- 🔐 **SSO Login / Logout** — redirect-based CAS authentication flow
- 🧩 **Vue 3 Plugin** — `app.use(CasPlugin, config)` for app-level setup
- ⚡ **Composables** — `useCasAuth()` and `useCasUser()` with full reactivity
- 🛡️ **Router Guards** — factory-based `beforeEach` guard with role support
- 📦 **Pinia Store** — optional store for Pinia-based state management
- 🎰 **Slot Component** — `<CasProtectedView>` for declarative access control
- 🌳 **Tree-Shakeable** — named exports only, import what you need
- 🔒 **Secure by Design** — tokens are validated server-side only
- 📝 **TypeScript First** — complete type definitions and JSDoc

---

## Installation

```bash
# npm
npm install @cas-system/vue-cas-client

# yarn
yarn add @cas-system/vue-cas-client

# pnpm
pnpm add @cas-system/vue-cas-client
```

### Peer Dependencies

| Package      | Version | Required |
| ------------ | ------- | -------- |
| `vue`        | ≥ 3.4   | ✅ Yes   |
| `vue-router` | ≥ 4.4   | Optional |
| `pinia`      | ≥ 2.2   | Optional |

---

## Auth Flow

```
┌──────────┐     1. login()      ┌────────────┐
│  Vue App │ ──────────────────▶ │ CAS Server │
│          │                     │ /sso/login  │
└──────────┘                     └─────┬──────┘
                                       │
                         2. User authenticates
                                       │
                                       ▼
┌──────────┐   3. Redirect with   ┌────────────┐
│  Vue App │ ◀───── ?token=JWT ── │ CAS Server │
│ /callback│                      └────────────┘
└────┬─────┘
     │
     │  4. POST token to backend
     ▼
┌──────────┐   5. Validate via         ┌────────────┐
│ Your     │ ─ POST /api/validate-token ▶│ CAS Server │
│ Backend  │   { token, client_id,     │            │
│          │     client_secret }       │            │
│          │ ◀ { valid, user,          │            │
│          │     expires_at } ─────────│            │
└────┬─────┘                           └────────────┘
     │
     │  6. Return validated user
     ▼
┌──────────┐
│  Vue App │  ← session stored in sessionStorage
│          │
└──────────┘
```

> **Security**: The JWT is **never** validated in the browser. Your backend
> receives the token, combines it with `client_id` + `client_secret`, and
> forwards it to the CAS server. This prevents exposing the `client_secret`.

---

## Quick Start

### 1. Install the plugin

```ts
// main.ts
import { createApp } from 'vue';
import { CasPlugin } from '@cas-system/vue-cas-client';
import App from './App.vue';

const app = createApp(App);

app.use(CasPlugin, {
  serverUrl: 'https://cas.example.com',
  clientId: 'my-vue-app',
  callbackUrl: 'https://my-app.com/auth/callback',
  backendValidateUrl: '/api/auth/validate',
});

app.mount('#app');
```

### 2. Use in components

```vue
<script setup lang="ts">
import { useCasAuth } from '@cas-system/vue-cas-client';

const { user, isAuthenticated, login, logout } = useCasAuth();
</script>

<template>
  <div v-if="isAuthenticated">
    <p>Welcome, {{ user?.username }}!</p>
    <button @click="logout()">Logout</button>
  </div>
  <div v-else>
    <button @click="login()">Login with SSO</button>
  </div>
</template>
```

### 3. Create a callback page

```vue
<!-- views/AuthCallback.vue -->
<script setup lang="ts">
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCasAuth } from '@cas-system/vue-cas-client';

const router = useRouter();
const { handleCallback, error } = useCasAuth();

onMounted(async () => {
  try {
    await handleCallback();
    router.push('/dashboard');
  } catch (e) {
    console.error('Auth failed:', e);
  }
});
</script>

<template>
  <div v-if="error">{{ error }}</div>
  <div v-else>Authenticating…</div>
</template>
```

---

## Plugin Setup

### CasPlugin Options

```ts
interface CasPluginOptions {
  /** Base URL of the CAS server (no trailing slash). */
  serverUrl: string;

  /** The client_id registered on the CAS server. */
  clientId: string;

  /** Post-login redirect URL. Defaults to `{origin}/auth/callback`. */
  callbackUrl?: string;

  /** Your backend's token validation endpoint. */
  backendValidateUrl?: string;

  /**
   * Auto-handle callback if a `?token=` is present in the URL.
   * @default true
   */
  autoHandleCallback?: boolean;
}
```

### Auto Callback Handling

By default, the plugin checks the URL for a `?token=` query parameter on
install and automatically validates it. Set `autoHandleCallback: false` to
disable this and handle callbacks manually.

---

## Composables

### `useCasAuth()`

Primary composable for authentication state and actions.

```ts
const {
  user,            // ComputedRef<CasUser | null>
  isAuthenticated, // ComputedRef<boolean>
  isLoading,       // ComputedRef<boolean>
  error,           // ComputedRef<string | null>
  login,           // (returnUrl?: string) => void
  logout,          // (redirectUrl?: string) => Promise<void>
  handleCallback,  // () => Promise<CasUser>
} = useCasAuth();
```

### `useCasUser()`

User-focused composable with reactive role checks.

```ts
const {
  user,        // ComputedRef<CasUser | null>
  roles,       // ComputedRef<string[]>
  hasRole,     // (role: string) => ComputedRef<boolean>
  hasAnyRole,  // (roles: string[]) => ComputedRef<boolean>
  hasAllRoles, // (roles: string[]) => ComputedRef<boolean>
} = useCasUser();

// Usage in templates:
const isAdmin = hasRole('admin');       // ComputedRef<boolean>
const canEdit = hasAnyRole(['editor', 'admin']);
```

---

## Vue Router Integration

### 1. Define routes with `meta`

```ts
// router/index.ts
import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  {
    path: '/',
    component: () => import('./views/Home.vue'),
  },
  {
    path: '/auth/callback',
    component: () => import('./views/AuthCallback.vue'),
  },
  {
    path: '/dashboard',
    component: () => import('./views/Dashboard.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/admin',
    component: () => import('./views/Admin.vue'),
    meta: { requiresAuth: true, roles: ['admin'] },
  },
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
});
```

### 2. Attach the guard

```ts
// main.ts
import { createApp } from 'vue';
import { CasPlugin, createCasAuthGuard, CAS_AUTH_KEY } from '@cas-system/vue-cas-client';
import { router } from './router';
import App from './App.vue';

const app = createApp(App);

app.use(CasPlugin, {
  serverUrl: 'https://cas.example.com',
  clientId: 'my-vue-app',
  backendValidateUrl: '/api/auth/validate',
});

// Access the CAS context from the app's provides
const casContext = app._context.provides[CAS_AUTH_KEY as symbol];

router.beforeEach(createCasAuthGuard(casContext, {
  redirectToLogin: true,    // Redirect to CAS if not authenticated
  // roles: ['user'],       // Optional global role requirement
}));

app.use(router);
app.mount('#app');
```

### Guard Options

```ts
interface CasGuardOptions {
  /** Override the CAS login URL. */
  loginUrl?: string;

  /** Global roles required for all guarded routes. */
  roles?: string[];

  /**
   * Redirect to CAS login when unauthenticated.
   * @default true
   */
  redirectToLogin?: boolean;
}
```

---

## Pinia Store

The `useCasStore()` Pinia store is an **optional** alternative for apps
already using Pinia for state management.

### Setup

```ts
// main.ts
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';

const app = createApp(App);
app.use(createPinia());
app.mount('#app');
```

### Usage

```vue
<script setup lang="ts">
import { onMounted } from 'vue';
import { useCasStore } from '@cas-system/vue-cas-client';

const auth = useCasStore();

onMounted(() => {
  auth.init({
    serverUrl: 'https://cas.example.com',
    clientId: 'my-vue-app',
    backendValidateUrl: '/api/auth/validate',
  });
});
</script>

<template>
  <div v-if="auth.isAuthenticated">
    <p>Hello, {{ auth.currentUser?.username }}</p>
    <p v-if="auth.hasRole('admin')">You are an admin!</p>
    <button @click="auth.logout()">Logout</button>
  </div>
  <div v-else>
    <button @click="auth.login()">Login</button>
  </div>
</template>
```

### Store API

| Type     | Name              | Description                          |
| -------- | ----------------- | ------------------------------------ |
| State    | `user`            | `CasUser \| null`                    |
| State    | `token`           | `string \| null`                     |
| State    | `isAuthenticated` | `boolean`                            |
| State    | `isLoading`       | `boolean`                            |
| State    | `error`           | `string \| null`                     |
| Getter   | `currentUser`     | Alias for `user`                     |
| Getter   | `hasRole`         | `(role: string) => boolean`          |
| Getter   | `hasAnyRole`      | `(roles: string[]) => boolean`       |
| Action   | `init(config)`    | Initialise with CAS config           |
| Action   | `login(url?)`     | Redirect to CAS login                |
| Action   | `logout(url?)`    | Logout and clear session             |
| Action   | `handleCallback`  | Validate callback token              |
| Action   | `checkAuth`       | Re-check auth from sessionStorage    |

---

## CasProtectedView Component

A declarative, slot-based component for conditional rendering based on
authentication and role state.

### Basic Usage

```vue
<template>
  <CasProtectedView>
    <p>This is visible only to authenticated users.</p>

    <template #fallback>
      <p>Please log in to continue.</p>
    </template>
  </CasProtectedView>
</template>
```

### With Role Check

```vue
<template>
  <CasProtectedView :roles="['admin']">
    <AdminPanel />

    <template #fallback>
      <p>You do not have permission to view this.</p>
    </template>

    <template #loading>
      <LoadingSpinner />
    </template>
  </CasProtectedView>
</template>
```

### Auto-Redirect

```vue
<!-- Automatically redirects unauthenticated users to CAS login -->
<CasProtectedView redirect>
  <Dashboard />
</CasProtectedView>
```

### Props

| Prop       | Type       | Default | Description                                    |
| ---------- | ---------- | ------- | ---------------------------------------------- |
| `roles`    | `string[]` | `[]`    | Required roles (user must have **all**)         |
| `redirect` | `boolean`  | `false` | Auto-redirect to CAS login if unauthenticated  |

### Slots

| Slot       | Description                                |
| ---------- | ------------------------------------------ |
| `default`  | Rendered when authenticated & authorized   |
| `fallback` | Rendered when NOT authenticated/authorized |
| `loading`  | Rendered during auth operations            |

---

## Backend Setup

Your backend must expose a validation endpoint that the SDK calls. The
backend keeps the `client_secret` safe and forwards the token to CAS:

### Example (Node.js / Express)

```ts
// POST /api/auth/validate
app.post('/api/auth/validate', async (req, res) => {
  const { token } = req.body;

  try {
    const response = await fetch(`${CAS_SERVER_URL}/api/validate-token`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        token,
        client_id: process.env.CAS_CLIENT_ID,
        client_secret: process.env.CAS_CLIENT_SECRET,
      }),
    });

    if (!response.ok) {
      return res.status(401).json({ error: 'Invalid token' });
    }

    // CAS replies: { valid: true, user: { id, username, email }, expires_at }
    // The token is single-use — validate it exactly once here.
    const data = await response.json();
    return res.json({ user: data.user });
  } catch (err) {
    return res.status(500).json({ error: 'Validation failed' });
  }
});
```

### Example (Laravel)

```php
// routes/api.php
Route::post('/auth/validate', function (Request $request) {
    $response = Http::post(config('cas.server_url') . '/api/validate-token', [
        'token'         => $request->input('token'),
        'client_id'     => config('cas.client_id'),
        'client_secret' => config('cas.client_secret'),
    ]);

    if ($response->failed()) {
        return response()->json(['error' => 'Invalid token'], 401);
    }

    // CAS replies: { valid, user: { id, username, email }, expires_at }
    return response()->json(['user' => $response->json('user')]);
});
```

---

## API Reference

### `CasClient`

| Method                             | Returns         | Description                          |
| ---------------------------------- | --------------- | ------------------------------------ |
| `getLoginUrl(returnUrl?)`          | `string`        | Build the CAS login URL              |
| `login(returnUrl?)`                | `void`          | Redirect to CAS login                |
| `extractTokenFromUrl()`            | `string \| null`| Read token from URL query string     |
| `validateTokenViaBackend(token)`   | `Promise<CasUser>` | Validate via your backend        |
| `handleCallback()`                 | `Promise<CasUser>` | Full callback flow                |
| `getUser()`                        | `CasUser \| null`| Get user from sessionStorage        |
| `getToken()`                       | `string \| null`| Get JWT from sessionStorage          |
| `isAuthenticated()`                | `boolean`       | Check if session exists              |
| `logout(redirectUrl?)`             | `Promise<void>` | Logout and redirect                  |
| `userHasRole(role)`                | `boolean`       | Check single role                    |
| `userHasAnyRole(roles)`            | `boolean`       | Check any of roles                   |
| `userHasAllRoles(roles)`           | `boolean`       | Check all roles                      |
| `clearSession()`                   | `void`          | Clear sessionStorage                 |

### Interfaces

```ts
interface CasConfig {
  serverUrl: string;
  clientId: string;
  callbackUrl?: string;
  backendValidateUrl?: string;
}

interface CasUser {
  id: string | number;
  username: string;
  email: string;
  roles?: string[];
}

interface CasAuthState {
  user: CasUser | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
}
```

---

## Examples

### Protecting Routes with Roles

```ts
const routes = [
  {
    path: '/settings',
    component: Settings,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/users',
    component: UserManagement,
    meta: { requiresAuth: true, roles: ['admin'] },
  },
  {
    path: '/editor',
    component: Editor,
    meta: { requiresAuth: true, roles: ['editor'] },
  },
];
```

### Role-Based UI Rendering

```vue
<script setup lang="ts">
import { useCasUser } from '@cas-system/vue-cas-client';

const { user, hasRole, hasAnyRole } = useCasUser();

const isAdmin = hasRole('admin');
const canManageContent = hasAnyRole(['admin', 'editor']);
</script>

<template>
  <nav>
    <RouterLink to="/">Home</RouterLink>
    <RouterLink v-if="isAdmin" to="/admin">Admin</RouterLink>
    <RouterLink v-if="canManageContent" to="/editor">Editor</RouterLink>
  </nav>
</template>
```

### Using with Axios Interceptors

```ts
import axios from 'axios';

const api = axios.create({ baseURL: '/api' });

api.interceptors.request.use((config) => {
  const token = sessionStorage.getItem('cas_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

### Combining Plugin + Pinia Store

You can use both approaches in the same app. The plugin handles the
injection-based composables, and the Pinia store provides a global
reactive store.

```ts
// main.ts
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { CasPlugin } from '@cas-system/vue-cas-client';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(CasPlugin, {
  serverUrl: 'https://cas.example.com',
  clientId: 'my-app',
  backendValidateUrl: '/api/auth/validate',
});

app.mount('#app');
```

---

## License

MIT
