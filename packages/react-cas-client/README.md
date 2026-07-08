# @cas-system/react-cas-client

React SDK for integrating with the **One System CAS (Central Authentication System)** server. Provides hooks, components, and a context provider for seamless SSO authentication in React 18+ applications.

## Features

- 🔐 **SSO Login Flow** — Redirect-based login via the CAS server
- 🛡️ **Secure by Design** — Token validation is always delegated to your backend; secrets never reach the browser
- ⚛️ **React 18+ Hooks & Context** — `useCasAuth`, `useCasUser`, and `<CasProvider>`
- 🚪 **Route Protection** — `<CasProtectedRoute>` with role-based access control
- 🌳 **Tree-Shakeable** — Named exports only, `sideEffects: false`
- 📦 **Zero Dependencies** — Only peer-depends on `react >=18.2`

---

## Installation

```bash
npm install @cas-system/react-cas-client
# or
yarn add @cas-system/react-cas-client
# or
pnpm add @cas-system/react-cas-client
```

> **Peer dependency:** `react >= 18.2`

### Compatibility

| Requirement      | Version                                        |
| ---------------- | ---------------------------------------------- |
| React (peer)     | `>= 18.2`                                       |
| Package version  | `1.0.0`                                          |
| Built with       | TypeScript `6.x`                                 |
| Reference sample | Vite `6`, React `18.3`, Node `20`               |

---

## Quick Start

### 1. Wrap your app with `<CasProvider>`

```tsx
import { CasProvider } from '@cas-system/react-cas-client';

const casConfig = {
  serverUrl: 'https://cas.example.com',
  clientId: 'my-app',
  callbackUrl: 'https://myapp.com/auth/callback',
  backendValidateUrl: '/api/auth/validate',
};

function App() {
  return (
    <CasProvider
      config={casConfig}
      onAuthSuccess={(user) => console.log('Welcome,', user.username)}
      onAuthError={(err) => console.error('Auth failed:', err)}
    >
      <YourApp />
    </CasProvider>
  );
}
```

### 2. Use hooks in any component

```tsx
import { useCasAuth } from '@cas-system/react-cas-client';

function Header() {
  const { user, isAuthenticated, isLoading, login, logout } = useCasAuth();

  if (isLoading) return <p>Loading…</p>;

  if (!isAuthenticated) {
    return <button onClick={() => login()}>Sign in</button>;
  }

  return (
    <div>
      Welcome, {user?.username}!
      <button onClick={() => logout()}>Sign out</button>
    </div>
  );
}
```

### 3. Protect routes

```tsx
import { CasProtectedRoute } from '@cas-system/react-cas-client';

function AppRoutes() {
  return (
    <Routes>
      <Route path="/" element={<HomePage />} />
      <Route
        path="/dashboard"
        element={
          <CasProtectedRoute fallback={<Spinner />}>
            <Dashboard />
          </CasProtectedRoute>
        }
      />
      <Route
        path="/admin"
        element={
          <CasProtectedRoute
            roles={['admin']}
            unauthorizedComponent={<Forbidden />}
            fallback={<Spinner />}
          >
            <AdminPanel />
          </CasProtectedRoute>
        }
      />
    </Routes>
  );
}
```

---

## How It Works

```
┌──────────┐     1. login()      ┌────────────┐
│  React   │ ──────────────────► │ CAS Server │
│   App    │                     │ /sso/login  │
└──────────┘                     └─────┬──────┘
     ▲                                 │
     │  4. User + session established  │ 2. User authenticates
     │                                 │
┌────┴─────┐   3. POST /validate ┌────▼──────┐
│  React   │ ◄────────────────── │ Your      │
│   App    │ ──────────────────► │ Backend   │
│ callback │    { token }        │           │
└──────────┘                     └───────────┘
```

1. **`login()`** redirects the browser to the CAS SSO login page.
2. The user authenticates on the CAS server.
3. CAS redirects back to your `callbackUrl` with `?token=JWT_TOKEN`.
4. `<CasProvider>` automatically extracts the token and sends it to your backend (`backendValidateUrl`) for validation.
5. Your backend calls `POST {casServerUrl}/api/validate-token` with `{ token, client_id, client_secret }` and returns the user object.
6. The SDK stores the user in `sessionStorage` and updates React state.

> **Security:** The `client_secret` never leaves your backend. The browser only sends the JWT to *your* backend, which proxies the validation request.

---

## Configuration

### `CasConfig`

| Property             | Type     | Required | Description                                                                 |
| -------------------- | -------- | -------- | --------------------------------------------------------------------------- |
| `serverUrl`          | `string` | ✅       | Base URL of the CAS server (no trailing slash)                              |
| `clientId`           | `string` | ✅       | OAuth client ID registered with the CAS server                             |
| `callbackUrl`        | `string` | No       | URL the CAS server redirects to after login. Defaults to current page URL. |
| `backendValidateUrl` | `string` | No       | Your backend endpoint for token validation (e.g. `/api/auth/validate`)      |

---

## API Reference

### Provider

#### `<CasProvider>`

Wrap your app to provide CAS authentication context.

| Prop            | Type                      | Description                               |
| --------------- | ------------------------- | ----------------------------------------- |
| `config`        | `CasConfig`               | CAS configuration (required)              |
| `children`      | `ReactNode`               | Child components                          |
| `onAuthSuccess` | `(user: CasUser) => void` | Callback after successful authentication  |
| `onAuthError`   | `(error: string) => void` | Callback when authentication fails        |

---

### Hooks

#### `useCasAuth()`

Returns the full authentication state and actions.

```ts
const {
  user,            // CasUser | null
  isAuthenticated, // boolean
  isLoading,       // boolean
  error,           // string | null
  login,           // (returnUrl?: string) => void
  logout,          // (redirectUrl?: string) => Promise<void>
  hasRole,         // (role: string) => boolean
  hasAnyRole,      // (roles: string[]) => boolean
} = useCasAuth();
```

#### `useCasUser()`

Returns just user data and role helpers (no login/logout actions).

```ts
const {
  user,            // CasUser | null
  isAuthenticated, // boolean
  isLoading,       // boolean
  hasRole,         // (role: string) => boolean
  hasAnyRole,      // (roles: string[]) => boolean
  hasAllRoles,     // (roles: string[]) => boolean
} = useCasUser();
```

---

### Components

#### `<CasProtectedRoute>`

Protects children behind authentication. Redirects to CAS login if unauthenticated.

| Prop                    | Type        | Description                                           |
| ----------------------- | ----------- | ----------------------------------------------------- |
| `children`              | `ReactNode` | Content to render when authenticated                  |
| `fallback`              | `ReactNode` | Loading component (default: `null`)                   |
| `roles`                 | `string[]`  | Required roles (user needs at least one)              |
| `unauthorizedComponent` | `ReactNode` | Shown when user lacks roles (otherwise redirects)     |

#### `<CasLoginButton>`

A button that triggers CAS login on click. Accepts all standard `<button>` attributes.

| Prop        | Type        | Description                                    |
| ----------- | ----------- | ---------------------------------------------- |
| `returnUrl` | `string`    | URL to return to after login                   |
| `className` | `string`    | CSS class name(s)                              |
| `children`  | `ReactNode` | Button content (default: `"Sign in"`)          |
| `...rest`   | `ButtonHTMLAttributes` | Any standard button HTML attribute  |

```tsx
<CasLoginButton className="btn btn-primary">
  🔑 Log in with SSO
</CasLoginButton>
```

---

### Core Client

#### `CasClient`

Low-level client class for advanced use cases. Most users should use the hooks/provider instead.

```ts
import { CasClient } from '@cas-system/react-cas-client';

const client = new CasClient({
  serverUrl: 'https://cas.example.com',
  clientId: 'my-app',
  backendValidateUrl: '/api/auth/validate',
});

// Build login URL
const url = client.getLoginUrl('/dashboard');

// Redirect to CAS login
client.login();

// Extract token from callback URL
const token = client.extractTokenFromUrl();

// Validate token via backend
const user = await client.validateTokenViaBackend(token);

// Full callback flow (extract + validate + store + clean URL)
const user = await client.handleCallback();

// Session management
client.getUser();          // CasUser | null
client.getToken();         // string | null
client.isAuthenticated();  // boolean

// Logout
await client.logout('/login');

// Role checks
client.userHasRole('admin');
client.userHasAnyRole(['admin', 'editor']);
client.userHasAllRoles(['admin', 'editor']);
```

---

## Types

### `CasUser`

```ts
interface CasUser {
  id: string;
  username: string;
  email: string;
  roles?: string[];
}
```

### `CasAuthState`

```ts
interface CasAuthState {
  user: CasUser | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
}
```

---

## Examples

### Custom Login Flow

```tsx
import { useCasAuth } from '@cas-system/react-cas-client';

function LoginPage() {
  const { login, isAuthenticated, error } = useCasAuth();

  if (isAuthenticated) {
    return <Navigate to="/dashboard" />;
  }

  return (
    <div className="login-page">
      <h1>Welcome</h1>
      {error && <p className="error">{error}</p>}
      <button onClick={() => login('/dashboard')}>
        Sign in with SSO
      </button>
    </div>
  );
}
```

### Role-Based UI

```tsx
import { useCasUser } from '@cas-system/react-cas-client';

function Sidebar() {
  const { user, hasRole, hasAnyRole } = useCasUser();

  return (
    <nav>
      <a href="/dashboard">Dashboard</a>
      {hasAnyRole(['admin', 'manager']) && (
        <a href="/reports">Reports</a>
      )}
      {hasRole('admin') && (
        <a href="/admin">Admin Panel</a>
      )}
    </nav>
  );
}
```

### Backend Validate Endpoint (Express Example)

Your backend needs an endpoint that proxies token validation:

```ts
// server/routes/auth.ts
app.post('/api/auth/validate', async (req, res) => {
  const { token } = req.body;

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

  // CAS responds with { valid, user: { id, username, email }, expires_at }.
  // The SDK expects a bare CasUser, so return just the `user` object.
  const { user } = await response.json();
  res.json(user);
});
```

### Using with React Router

```tsx
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { CasProvider, CasProtectedRoute } from '@cas-system/react-cas-client';

function App() {
  return (
    <BrowserRouter>
      <CasProvider config={casConfig}>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/auth/callback" element={<AuthCallback />} />
          <Route
            path="/dashboard/*"
            element={
              <CasProtectedRoute fallback={<Loading />}>
                <DashboardRoutes />
              </CasProtectedRoute>
            }
          />
        </Routes>
      </CasProvider>
    </BrowserRouter>
  );
}

// The callback page just renders normally — CasProvider auto-handles the token
function AuthCallback() {
  return <p>Authenticating…</p>;
}
```

---

## Session Storage

User data and tokens are stored in `sessionStorage` (not `localStorage`):

- `cas_user` — JSON-serialized `CasUser` object
- `cas_token` — Raw JWT token string

This means sessions are scoped to the browser tab and cleared when the tab is closed.

---

## License

MIT
